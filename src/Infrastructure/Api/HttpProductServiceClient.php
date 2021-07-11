<?php

declare(strict_types=1);

namespace TmpApp\Infrastructure\Api;

use Exception;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

class HttpProductServiceClient
{
    private Client $gatewayClient;

    public function __construct(Client $gatewayClient)
    {
        $this->gatewayClient = $gatewayClient;
    }

    /**
     * @throws \JsonException
     */
    public function getAllAsync(int $offersPerRequest, int $concurrentRequests, callable $callback): void
    {
        $stop = false;
        $requestGenerator = static function (int $limit) use (&$stop) {
            $offset = 0;
            do {
                $body = json_encode(compact('limit', 'offset'), JSON_THROW_ON_ERROR);
                yield new Request('POST', '/v1.0/products/get-list-all', [], $body);
                $offset += $limit;
            } while (!$stop);
        };
        $pool = $this->getPool($requestGenerator, $callback, $offersPerRequest, $concurrentRequests);
        $pool->promise()->wait();
    }

    private function getPool(
        callable $requestGenerator,
        callable $callback,
        int $offersPerRequest,
        int $concurrentRequests
    ): Pool {
        return new Pool(
            new Client(
                [
                    'base_uri' => 'https://' . $this->gatewayClient->getHost() . ':' . $this->gatewayClient->getPort(),
                    'headers'  => [
                        'Content-Type' => 'application/json',
                        'App-Uid'      => $this->gatewayClient->getAppUid(),
                    ],
                ]
            ),
            $requestGenerator($offersPerRequest),
            [
                'concurrency' => $concurrentRequests,
                'fulfilled'   => function (Response $response) use ($callback, $offersPerRequest, &$stop) {
                    $body = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
                    $offers = $body['result']['offers'] ?? [];
                    if (!empty($offers)) {
                        $callback($offers);
                    }

                    if (count($offers) < $offersPerRequest) {
                        $stop = true;
                    }
                },
                'rejected'    => function (Exception $reason) {
                    throw $reason;
                },
            ]
        );
    }
}
