<?php

namespace Spatie\DbDumper;

use Spatie\DbDumper\Databases\MySql;
use Spatie\DbDumper\Databases\PostgreSql;
use Spatie\DbDumper\Exceptions\CannotCreateDumper;

class DbDumperFactory
{
    public function create(string $type)
    {
        $type = strtolower($type);

        if ($type === 'mysql') {
            return new MySql();
        }

        if ($type === 'pgsql') {
            return new PostgreSql();
        }

        throw CannotCreateDumper::unknownType($type);
    }
}
