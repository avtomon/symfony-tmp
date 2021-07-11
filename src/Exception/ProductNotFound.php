<?php

declare(strict_types=1);

namespace TmpApp\Exception;

use Symfony\Component\HttpFoundation\Response;

class ProductNotFound extends AbstractException
{
    protected const MESSAGE = 'Товар не найден.';
    protected const CODE = Response::HTTP_NOT_FOUND;
}