# Dump the contents of a database

[![Latest Version on Packagist](https://img.shields.io/packagist/v/spatie/db-dumper.svg?style=flat-square)](https://packagist.org/packages/spatie/db-dumper)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/spatie/db-dumper/master.svg?style=flat-square)](https://travis-ci.org/spatie/db-dumper)
[![SensioLabsInsight](https://img.shields.io/sensiolabs/i/bd8dcd6b-19db-4d65-9cdd-3b6ecb2626b1.svg?style=flat-square)](https://insight.sensiolabs.com/projects/bd8dcd6b-19db-4d65-9cdd-3b6ecb2626b1)
[![Quality Score](https://img.shields.io/scrutinizer/g/spatie/db-dumper.svg?style=flat-square)](https://scrutinizer-ci.com/g/spatie/db-dumper)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/db-dumper.svg?style=flat-square)](https://packagist.org/packages/spatie/db-dumper)

This repo contains an easy to use class to dump a database using PHP. Currently only MySQL is supported. Behind
the scences `mysqldump` is used.

Here a simple example of how to create a dump.

```php
Spatie\DbDumper\Databases\MySql::create()
    ->setDbName($databaseName)
    ->setUserName($userName)
    ->setPassword($password)
    ->dumpToFile('dump.sql');
```

Spatie is a webdesign agency based in Antwerp, Belgium. You'll find an overview of all our open source projects [on our website](https://spatie.be/opensource).

## Requirements
`mysqldump` should be installed.

## Installation

You can install the package via composer:
``` bash
$ composer require spatie/db-dumper
```

## Usage

This is the simplest way to create dump of the db:

```php
Spatie\DbDumper\Databases\MySql::create()
    ->setDbName($databaseName)
    ->setUserName($userName)
    ->setPassword($password)
    ->dumpToFile('dump.sql');
```

If the `mysqldump` binary is installed in a non default location you can let the package know by using the
`setDumpBinaryPath()`-function:

```php
Spatie\DbDumper\Databases\MySql::create()
    ->setDumpBinaryPath('/custom/location')
    ->setDbName($databaseName)
    ->setUserName($userName)
    ->setPassword($password)
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

## About Spatie
Spatie is a webdesign agency based in Antwerp, Belgium. You'll find an overview of all our open source projects [on our website](https://spatie.be/opensource).

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
