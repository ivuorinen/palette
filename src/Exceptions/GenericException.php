<?php

namespace ivuorinen\Palette\Exceptions;

use ErrorException;
use Throwable;

class GenericException extends ErrorException
{
    public function __construct(string $message, int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
