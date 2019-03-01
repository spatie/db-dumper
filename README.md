# Dump the contents of a database

[![Latest Version on Packagist](https://img.shields.io/packagist/v/spatie/db-dumper.svg?style=flat-square)](https://packagist.org/packages/spatie/db-dumper)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/spatie/db-dumper/master.svg?style=flat-square)](https://travis-ci.org/spatie/db-dumper)
[![Quality Score](https://img.shields.io/scrutinizer/g/spatie/db-dumper.svg?style=flat-square)](https://scrutinizer-ci.com/g/spatie/db-dumper)
[![StyleCI](https://styleci.io/repos/49829051/shield?branch=master)](https://styleci.io/repos/49829051)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/db-dumper.svg?style=flat-square)](https://packagist.org/packages/spatie/db-dumper)

This repo contains an easy to use class to dump a database using PHP. Currently MySQL, PostgreSQL, SQLite and MongoDB are supported. Behind
the scenes `mysqldump`, `pg_dump`, `sqlite3` and `mongodump` are used.

Here's are simple examples of how to create a database dump with different drivers:

**MySQL**

```php
Spatie\DbDumper\Databases\MySql::create()
    ->setDbName($databaseName)
    ->setUserName($userName)
    ->setPassword($password)
    ->dumpToFile('dump.sql');
```

**PostgreSQL**

```php
Spatie\DbDumper\Databases\PostgreSql::create()
    ->setDbName($databaseName)
    ->setUserName($userName)
    ->setPassword($password)
    ->dumpToFile('dump.sql');
```

**SQLite**

```php
Spatie\DbDumper\Databases\Sqlite::create()
    ->setDbName($pathToDatabaseFile)
    ->dumpToFile('dump.sql');
```

**MongoDB**

```php
Spatie\DbDumper\Databases\MongoDb::create()
    ->setDbName($databaseName)
    ->setUserName($userName)
    ->setPassword($password)
    ->dumpToFile('dump.gz');
```

Spatie is a webdesign agency based in Antwerp, Belgium. You'll find an overview of all our open source projects [on our website](https://spatie.be/opensource).

## Requirements
For dumping MySQL-db's `mysqldump` should be installed.

For dumping PostgreSQL-db's `pg_dump` should be installed.

For dumping SQLite-db's `sqlite3` should be installed.

For dumping MongoDB-db's `mongodump` should be installed.

## Installation

You can install the package via composer:
``` bash
$ composer require spatie/db-dumper
```

## Usage

This is the simplest way to create a dump of a MySql db:

```php
Spatie\DbDumper\Databases\MySql::create()
    ->setDbName($databaseName)
    ->setUserName($userName)
    ->setPassword($password)
    ->dumpToFile('dump.sql');
```

If you're working with PostgreSQL just use that dumper, most methods are available on both the MySql. and PostgreSql-dumper.

```php
Spatie\DbDumper\Databases\PostgreSql::create()
    ->setDbName($databaseName)
    ->setUserName($userName)
    ->setPassword($password)
    ->dumpToFile('dump.sql');
```

If the `mysqldump` (or `pg_dump`) binary is installed in a non default location you can let the package know by using the`setDumpBinaryPath()`-function:

```php
Spatie\DbDumper\Databases\MySql::create()
    ->setDumpBinaryPath('/custom/location')
    ->setDbName($databaseName)
    ->setUserName($userName)
    ->setPassword($password)
    ->dumpToFile('dump.sql');
```

### Dump specific tables

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

### Excluding tables from the dump

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

### Do not write CREATE TABLE statements that create each dumped table.
```php
$dumpCommand = MySql::create()
    ->setDbName('dbname')
    ->setUserName('username')
    ->setPassword('password')
    ->doNotCreateTables()
    ->getDumpCommand('dump.sql', 'credentials.txt');
```

### Adding extra options
If you want to add an arbitrary option to the dump command you can use `addExtraOption`

```php
$dumpCommand = MySql::create()
    ->setDbName('dbname')
    ->setUserName('username')
    ->setPassword('password')
    ->addExtraOption('--xml')
    ->getDumpCommand('dump.sql', 'credentials.txt');
```

If you're working with MySql you can set the database name using `--databases` as an extra option. This is particularly useful when used in conjunction with the `--add-drop-database` `mysqldump` option (see the [mysqldump docs](https://dev.mysql.com/doc/refman/5.7/en/mysqldump.html#option_mysqldump_add-drop-database)).

```php
$dumpCommand = MySql::create()
    ->setUserName('username')
    ->setPassword('password')
    ->addExtraOption('--databases dbname')
    ->addExtraOption('--add-drop-database')
    ->getDumpCommand('dump.sql', 'credentials.txt');
```

With MySql, you also have the option to use the `--all-databases` extra option. This is useful when you want to run a full backup of all the databases in the specified MySQL connection.

```php
$dumpCommand = MySql::create()
    ->setUserName('username')
    ->setPassword('password')
    ->addExtraOption('--all-databases')
    ->getDumpCommand('dump.sql', 'credentials.txt');
```

Please note that using the `->addExtraOption('--databases dbname')` or `->addExtraOption('--all-databases')` will override the database name set on a previous `->setDbName()` call.

### Using compression
If you want to compress the outputted file, you can use one of the compressors and the resulted dump file will be compressed.

There is one compressor that comes out of the box: `GzipCompressor`. It will compress your db dump with `gzip`. Make sure `gzip` is installed on your system before using this.

```php
$dumpCommand = MySql::create()
    ->setDbName('dbname')
    ->setUserName('username')
    ->setPassword('password')
    ->useCompressor(new GzipCompressor())
    ->dumpToFile('dump.sql.gz');
```

### Creating your own compressor

You can create you own compressor implementing the `Compressor` interface. Here's how that interface looks like:

```php
namespace Spatie\DbDumper\Compressors;

interface Compressor
{
    public function useCommand(): string;
    
    public function useExtension(): string;
}
```

The `useCommand` should simply return the compression command the db dump will get pumped to. Here's the implementation of `GzipCompression`.

```php
namespace Spatie\DbDumper\Compressors;

class GzipCompressor implements Compressor
{
    public function useCommand(): string
    {
        return 'gzip';
    }
    
    public function useExtension(): string
    {
        return 'gz';
    }
}
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

## Postcardware

You're free to use this package, but if it makes it to your production environment we highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using.

Our address is: Spatie, Samberstraat 69D, 2060 Antwerp, Belgium.

We publish all received postcards [on our company website](https://spatie.be/en/opensource/postcards).

## Credits

- [Freek Van der Herten](https://github.com/freekmurze)
- [All Contributors](../../contributors)

Initial PostgreSQL support was contributed by [Adriano Machado](https://github.com/ammachado). SQlite support was contributed by [Peter Matseykanets](https://twitter.com/pmatseykanets).

## Support us

Spatie is a webdesign agency based in Antwerp, Belgium. You'll find an overview of all our open source projects [on our website](https://spatie.be/opensource).

Does your business depend on our contributions? Reach out and support us on [Patreon](https://www.patreon.com/spatie).
All pledges will be dedicated to allocating workforce on maintenance and new awesome stuff.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
