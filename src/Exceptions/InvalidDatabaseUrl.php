<?php

namespace Spatie\DbDumper\Exceptions;

use Exception;

class InvalidDatabaseUrl extends Exception
{
    public static function invalidUrl(string $databaseUrl): static
    {
        return new static("Database URL `{$databaseUrl}` is invalid and cannot be parsed.");
    }
}
