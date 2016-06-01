# Dump the contents of a database

[![Latest Version on Packagist](https://img.shields.io/packagist/v/spatie/db-dumper.svg?style=flat-square)](https://packagist.org/packages/spatie/db-dumper)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/spatie/db-dumper/master.svg?style=flat-square)](https://travis-ci.org/spatie/db-dumper)
[![SensioLabsInsight](https://img.shields.io/sensiolabs/i/bd8dcd6b-19db-4d65-9cdd-3b6ecb2626b1.svg?style=flat-square)](https://insight.sensiolabs.com/projects/bd8dcd6b-19db-4d65-9cdd-3b6ecb2626b1)
[![Quality Score](https://img.shields.io/scrutinizer/g/spatie/db-dumper.svg?style=flat-square)](https://scrutinizer-ci.com/g/spatie/db-dumper)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/db-dumper.svg?style=flat-square)](https://packagist.org/packages/spatie/db-dumper)

This repo contains an easy to use class to dump a database using PHP. Currently MySQL and PostgreSQL are supported. Behind
the scences `mysqldump` and `pg_dump` are used.

Here's a simple example of how to create a dump of MySQL-datbase:

```php
Spatie\DbDumper\Databases\MySql::create()
    ->setDbName($databaseName)
    ->setUserName($userName)
    ->setPassword($password)
    ->dumpToFile('dump.sql');
```

And here's the PostgreSQL version of that:

```php
Spatie\DbDumper\Databases\PostgreSql::create()
    ->setDbName($databaseName)
    ->setUserName($userName)
    ->setPassword($password)
    ->dumpToFile('dump.sql');
```

Spatie is a webdesign agency based in Antwerp, Belgium. You'll find an overview of all our open source projects [on our website](https://spatie.be/opensource).

## Requirements
For dumping MySQL-db's `mysqldump` should be installed
For dumping PostgreSQL-db's `pg_dump` should be installed.

## Installation

You can install the package via composer:
``` bash
$ composer require spatie/db-dumper
```

## Usage

### MySQL

This is the simplest way to create a dump of the db:

```php
Spatie\DbDumper\Databases\MySql::create()
    ->setDbName($databaseName)
    ->setUserName($userName)
    ->setPassword($password)
    ->dumpToFile('dump.sql');
```

If the `mysqldump`  binary is installed in a non default location you can let the package know by using the`setDumpBinaryPath()`-function:

```php
Spatie\DbDumper\Databases\MySql::create()
    ->setDumpBinaryPath('/custom/location')
    ->setDbName($databaseName)
    ->setUserName($userName)
    ->setPassword($password)
    ->dumpToFile('dump.sql');
```

#### Dump specific tables

Using an array:

```php
Spatie\DbDumper\Databases\MySql::create()
    ->setDbName($databaseName)
    ->setUserName($userName)
    ->setPassword($password)
    ->includeTables(['table1', 'table2', 'table3'])
    ->dumpToFile('dump.sql');
```
Using a string:

```php
Spatie\DbDumper\Databases\MySql::create()
    ->setDbName($databaseName)
    ->setUserName($userName)
    ->setPassword($password)
    ->includeTables('table1, table2, table3')
    ->dumpToFile('dump.sql');
```

#### Excluding tables from the dump

Using an array:

```php
Spatie\DbDumper\Databases\MySql::create()
    ->setDbName($databaseName)
    ->setUserName($userName)
    ->setPassword($password)
    ->excludeTables(['table1', 'table2', 'table3'])
    ->dumpToFile('dump.sql');
```
Using a string:

```php
Spatie\DbDumper\Databases\MySql::create()
    ->setDbName($databaseName)
    ->setUserName($userName)
    ->setPassword($password)
    ->excludeTables('table1, table2, table3')
    ->dumpToFile('dump.sql');
```

### PostreSQL

This is the simplest way to create a dump of the db:

```php
Spatie\DbDumper\Databases\PostgreSql::create()
    ->setDbName($databaseName)
    ->setUserName($userName)
    ->setPassword($password)
    ->dumpToFile('dump.sql');
```

If the `pg_dump` binary is installed in a non default location you can let the package know by using the `setDumpBinaryPath()`-function:

```php
Spatie\DbDumper\Databases\PostgreSql::create()
    ->setDumpBinaryPath('/custom/location')
    ->setDbName($databaseName)
    ->setUserName($userName)
    ->setPassword($password)
    ->dumpToFile('dump.sql');
```
#### Dump specific tables

Using an array:

```php
Spatie\DbDumper\Databases\PostgreSql::create()
    ->setDbName($databaseName)
    ->setUserName($userName)
    ->setPassword($password)
    ->setTables(array('table1', 'table2', 'table3'))
    ->dumpToFile('dump.sql');
```
Using a string:

```php
Spatie\DbDumper\Databases\PostgreSql::create()
    ->setDbName($databaseName)
    ->setUserName($userName)
    ->setPassword($password)
    ->setTables('table1 table2 table3')
    ->dumpToFile('dump.sql');
```

#### Excluding tables from the dump

Using an array:

```php
Spatie\DbDumper\Databases\PostgreSql::create()
    ->setDbName($databaseName)
    ->setUserName($userName)
    ->setPassword($password)
    ->excludeTables(array('table1', 'table2', 'table3'))
    ->dumpToFile('dump.sql');
```
Using a string:

```php
Spatie\DbDumper\Databases\PostgreSql::create()
    ->setDbName($databaseName)
    ->setUserName($userName)
    ->setPassword($password)
    ->excludeTables('table1 table2 table3')
    ->dumpToFile('dump.sql');
```


## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email freek@spatie.be instead of using the issue tracker.

## Credits

- [Freek Van der Herten](https://github.com/freekmurze)
- [All Contributors](../../contributors)

Initial PostgreSQL support was contributed by [Adriano Machado](https://github.com/ammachado).

## About Spatie
Spatie is a webdesign agency based in Antwerp, Belgium. You'll find an overview of all our open source projects [on our website](https://spatie.be/opensource).

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
