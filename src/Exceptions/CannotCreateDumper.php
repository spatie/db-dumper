<?php

namespace Spatie\DbDumper\Exceptions;

use Exception;

class CannotCreateDumper extends Exception
{
    public static function unknownType(string $type): CannotCreateDumper
    {
        return new static("Cannot create a dumper of type `{$type}`. Using `mysql` or `pgsql`.");
    }
}
