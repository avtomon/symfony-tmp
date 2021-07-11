<?php

declare(strict_types=1);

namespace TmpApp\EventSubscriber;

use JMS\Serializer\ArrayTransformerInterface;
use TmpApp\DTO\AbstractRequestDto;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class EventSubscriber implements EventSubscriberInterface
{
    private const ALLOWED_ERROR_STATUS_CODES = [
        Response::HTTP_BAD_REQUEST,
        Response::HTTP_UNAUTHORIZED,
        Response::HTTP_NOT_FOUND,
    ];

    private ArrayTransformerInterface $serializer;

    private ValidatorInterface $validator;

    private string $token;

    public function __construct(ArrayTransformerInterface $serializer, ValidatorInterface $validator, string $token)
    {
        $this->serializer = $serializer;
        $this->validator  = $validator;
        $this->token = $token;
    }

    public function onKernelController(ControllerEvent $event): void
    {
        $token = $event->getRequest()->headers->get('Authorization');
        if (empty($token) || $token !== $this->token) {
            throw new UnauthorizedHttpException('', 'Неверный токен доступа.');
        }
    }

    private function generateValidationErrorIfExist(ConstraintViolationListInterface $violations): void
    {
        if ($violations->count() !== 0) {
            $violationMessage = '';

            /** @var ConstraintViolation $violation */
            foreach ($violations as $violation) {
                $violationMessage .= $violation->getPropertyPath() . ': ' . $violation->getMessage() . PHP_EOL;
            }

            throw new BadRequestHttpException($violationMessage);
        }
    }

    public function onKernelControllerArguments(ControllerArgumentsEvent $event): void
    {
        $request = $event->getRequest();
        $bodyParams = [];
        try {
            if ($request->getContent()) {
                if ($request->getContentType() !== 'json') {
                    throw new HttpException(Response::HTTP_UNSUPPORTED_MEDIA_TYPE, 'Content-Type must be JSON');
                }

                $bodyParams = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
            }

            $params = array_merge($request->attributes->all(), $bodyParams);

            $arguments = $event->getArguments();

            foreach ($arguments as &$argument) {
                if (!is_subclass_of($argument, AbstractRequestDto::class)) {
                    continue;
                }

                $argument = $this->serializer->fromArray($params, get_class($argument));

                $this->generateValidationErrorIfExist($this->validator->validate($argument, null, ['type']));
                $this->generateValidationErrorIfExist($this->validator->validate($argument));
            }
            unset($argument);

            $event->setArguments($arguments);
        } catch (\JsonException $e) {
            throw new BadRequestHttpException('Invalid JSON content: ' . $e->getMessage());
        }
    }

    /**
     * @throws \JsonException
     */
    public function onKernelView(ViewEvent $event): void
    {
        $data = json_encode($event->getControllerResult(), JSON_THROW_ON_ERROR);
        $event->setResponse(
            new JsonResponse(
                $data,
                Response::HTTP_OK,
                [
                    'Content-Type'   => 'application/json',
                    'Content-Length' => strlen($data),
                ],
                true
            )
        );
    }

    /**
     * @throws \JsonException
     */
    public function onKernelException(ExceptionEvent $event): void
    {
        $e = $event->getThrowable();
        $data = ['code' => $e->getCode(), 'description' => $e->getMessage(),];
        $statusCode = (
            $e instanceof HttpException
            && in_array($e->getStatusCode(), static::ALLOWED_ERROR_STATUS_CODES, true)
        )
            ? $e->getStatusCode()
            : Response::HTTP_INTERNAL_SERVER_ERROR;

        if ($e instanceof UnauthorizedHttpException) {
            $data = ['reason' => $e->getMessage(),];
        }

        $data = json_encode($data, JSON_THROW_ON_ERROR);

        $event->setResponse(
            new JsonResponse(
                $data,
                $statusCode,
                [
                    'Content-Type'   => 'application/json',
                    'Content-Length' => strlen($data),
                ],
                true
            )
        );
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
            KernelEvents::CONTROLLER_ARGUMENTS => 'onKernelControllerArguments',
            KernelEvents::VIEW => 'onKernelView',
            KernelEvents::EXCEPTION => [['onKernelException', -1]],
        ];
    }
}
