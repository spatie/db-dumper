# Dump the contents of a database

[![Latest Version on Packagist](https://img.shields.io/packagist/v/spatie/db-dumper.svg?style=flat-square)](https://packagist.org/packages/spatie/db-dumper)
![run-tests](https://github.com/spatie/db-dumper/workflows/run-tests/badge.svg)
[![MIT Licensed](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/db-dumper.svg?style=flat-square)](https://packagist.org/packages/spatie/db-dumper)

This repo contains an easy to use class to dump a database using PHP. Currently MySQL, PostgreSQL, SQLite and MongoDB are supported. Behind the scenes `mysqldump`, `pg_dump`, `sqlite3` and `mongodump` are used.

Here are simple examples of how to create a database dump with different drivers:

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

⚠️ Sqlite version 3.32.0 is required when using the `includeTables` option.

**MongoDB**

```php
Spatie\DbDumper\Databases\MongoDb::create()
    ->setDbName($databaseName)
    ->setUserName($userName)
    ->setPassword($password)
    ->dumpToFile('dump.gz');
```

## Support us

[<img src="https://github-ads.s3.eu-central-1.amazonaws.com/db-dumper.jpg?t=1" width="419px" />](https://spatie.be/github-ad-click/db-dumper)

We invest a lot of resources into creating [best in class open source packages](https://spatie.be/open-source). You can support us by [buying one of our paid products](https://spatie.be/open-source/support-us).

We highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using. You'll find our address on [our contact page](https://spatie.be/about-us). We publish all received postcards on [our virtual postcard wall](https://spatie.be/open-source/postcards).

## Requirements

For dumping MySQL-db's `mysqldump` should be installed.

For dumping PostgreSQL-db's `pg_dump` should be installed.

For dumping SQLite-db's `sqlite3` should be installed.

For dumping MongoDB-db's `mongodump` should be installed.

For compressing dump files, `gzip` and/or `bzip2` should be installed.

## Installation

You can install the package via composer:

```bash
composer require spatie/db-dumper
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

If your application is deployed and you need to change the host (default is 127.0.0.1), you can add the `setHost()`-function:

```php
Spatie\DbDumper\Databases\MySql::create()
    ->setDbName($databaseName)
    ->setUserName($userName)
    ->setPassword($password)
    ->setHost($host)
    ->dumpToFile('dump.sql');
```

### Handling AUTO_INCREMENT Values in Dumps

When creating a database dump, you might need to control the inclusion of AUTO_INCREMENT values. This can be crucial for avoiding primary key conflicts or for maintaining ID consistency when transferring data across environments.

#### Skipping AUTO_INCREMENT Values

To omit the AUTO_INCREMENT values from the tables in your dump, use the skipAutoIncrement method. This is particularly useful to prevent conflicts when importing the dump into another database where those specific AUTO_INCREMENT values might already exist, or when the exact values are not relevant.

```php
Spatie\DbDumper\Databases\MySql::create()
    ->setDbName('dbname')
    ->setUserName('username')
    ->setPassword('password')
    ->skipAutoIncrement()
    ->dumpToFile('dump.sql');
```

### Including AUTO_INCREMENT values in the dump

By default, the AUTO_INCREMENT values are included in the dump. However, if you previously used the skipAutoIncrement method and wish to ensure that the AUTO_INCREMENT values are included in a subsequent dump, use the dontSkipAutoIncrement method to explicitly include them.

```php
Spatie\DbDumper\Databases\MySql::create()
    ->setDbName('dbname')
    ->setUserName('username')
    ->setPassword('password')
    ->dontSkipAutoIncrement()
    ->dumpToFile('dump.sql');
```

### Use a Database URL

In some applications or environments, database credentials are provided as URLs instead of individual components. In this case, you can use the `setDatabaseUrl` method instead of the individual methods.

```php
Spatie\DbDumper\Databases\MySql::create()
    ->setDatabaseUrl($databaseUrl)
    ->dumpToFile('dump.sql');
```

When providing a URL, the package will automatically parse it and provide the individual components to the applicable dumper.

For example, if you provide the URL `mysql://username:password@hostname:3306/dbname`, the dumper will use the `hostname` host, running on port `3306`, and will connect to `dbname` with `username` and `password`.

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

### Don't use column_statics table with some old version of MySql service.

In order to use "_--column-statistics=0_" as option in mysqldump command you can use _doNotUseColumnStatistics()_ method.

If you have installed _mysqldump 8_, it queries by default _column_statics_ table in _information_schema_ database.
In some old version of MySql (service) like 5.7, this table doesn't exist. So you could have an exception during the execution of mysqldump. To avoid this, you could use _doNotUseColumnStatistics()_ method.

```php
Spatie\DbDumper\Databases\MySql::create()
    ->setDbName($databaseName)
    ->setUserName($userName)
    ->setPassword($password)
    ->doNotUseColumnStatistics()
    ->dumpToFile('dump.sql');
```

### Excluding tables from the dump

You can exclude tables from the dump by using an array:

```php
Spatie\DbDumper\Databases\MySql::create()
    ->setDbName($databaseName)
    ->setUserName($userName)
    ->setPassword($password)
    ->excludeTables(['table1', 'table2', 'table3'])
    ->dumpToFile('dump.sql');
```

Or by using a string:

```php
Spatie\DbDumper\Databases\MySql::create()
    ->setDbName($databaseName)
    ->setUserName($userName)
    ->setPassword($password)
    ->excludeTables('table1, table2, table3')
    ->dumpToFile('dump.sql');
```

### Do not write CREATE TABLE statements that create each dumped table

You can use `doNotCreateTables` to prevent writing create statements.

```php
$dumpCommand = MySql::create()
    ->setDbName('dbname')
    ->setUserName('username')
    ->setPassword('password')
    ->doNotCreateTables()
    ->getDumpCommand('dump.sql', 'credentials.txt');
```

### Do not write row data

You can use `doNotDumpData` to prevent writing row data.


```php
$dumpCommand = MySql::create()
    ->setDbName('dbname')
    ->setUserName('username')
    ->setPassword('password')
    ->doNotDumpData()
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

If you want the output file to be compressed, you can use a compressor class.

There are two compressors that come out of the box:

-   `GzipCompressor` - This will compress your db dump with `gzip`. Make sure `gzip` is installed on your system before using this.
-   `Bzip2Compressor` - This will compress your db dump with `bzip2`. Make sure `bzip2` is installed on your system before using this.

```php
$dumpCommand = MySql::create()
    ->setDbName('dbname')
    ->setUserName('username')
    ->setPassword('password')
    ->useCompressor(new GzipCompressor()) // or `new Bzip2Compressor()`
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

## Testing

```bash
$ composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](https://github.com/spatie/.github/blob/main/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

-   [Freek Van der Herten](https://github.com/freekmurze)
-   [All Contributors](../../contributors)

Initial PostgreSQL support was contributed by [Adriano Machado](https://github.com/ammachado). SQlite support was contributed by [Peter Matseykanets](https://twitter.com/pmatseykanets).

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
