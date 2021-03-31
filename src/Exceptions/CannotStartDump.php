<?php

namespace Spatie\DbDumper\Exceptions;

use Exception;

class CannotStartDump extends Exception
{
    public static function emptyParameter(string $name): static
    {
        return new static("Parameter `{$name}` cannot be empty.");
    }
}
