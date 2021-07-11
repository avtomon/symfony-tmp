<?php

declare(strict_types=1);

namespace TmpApp\Exception;

use RuntimeException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class AbstractException extends RuntimeException
{
    protected const MESSAGE = 'Unknown error.';
    protected const CODE = Response::HTTP_INTERNAL_SERVER_ERROR;

    public function __construct($message = '', $code = 0, Throwable $previous = null)
    {
        parent::__construct($message ?: static::MESSAGE, $code ?: static::CODE, $previous);
    }
}