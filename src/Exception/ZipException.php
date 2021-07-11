<?php

declare(strict_types=1);

namespace TmpApp\Exception;

class ZipException extends AbstractException
{
    protected const MESSAGE = 'Ошибка zip-архива.';
}