# Changelog

All notable changes to `db-dumper` will be documented in this file

## 3.4.1 - 2023-12-16

### What's Changed

* Bump dependabot/fetch-metadata from 1.5.1 to 1.6.0 by @dependabot in https://github.com/spatie/db-dumper/pull/198
* Allow symfony/process 7.x by @thecaliskan in https://github.com/spatie/db-dumper/pull/202

### New Contributors

* @thecaliskan made their first contribution in https://github.com/spatie/db-dumper/pull/202

**Full Changelog**: https://github.com/spatie/db-dumper/compare/3.4.0...3.4.1

## 3.4.0 - 2023-06-27

### What's Changed

- Bump dependabot/fetch-metadata from 1.4.0 to 1.5.1 by @dependabot in https://github.com/spatie/db-dumper/pull/194
- Add support for database URLs by @mikerockett in https://github.com/spatie/db-dumper/pull/196

### New Contributors

- @mikerockett made their first contribution in https://github.com/spatie/db-dumper/pull/196

**Full Changelog**: https://github.com/spatie/db-dumper/compare/3.3.1...3.4.0

## 3.3.1 - 2023-05-02

### What's Changed

- PHP 8.2 Build by @erikn69 in https://github.com/spatie/db-dumper/pull/180
- Add Dependabot Automation by @patinthehat in https://github.com/spatie/db-dumper/pull/182
- Bump dependabot/fetch-metadata from 1.3.5 to 1.3.6 by @dependabot in https://github.com/spatie/db-dumper/pull/186
- Convert all tests to Pest by @alexmanase in https://github.com/spatie/db-dumper/pull/188
- Bump actions/checkout from 2 to 3 by @dependabot in https://github.com/spatie/db-dumper/pull/183
- Bump dependabot/fetch-metadata from 1.3.6 to 1.4.0 by @dependabot in https://github.com/spatie/db-dumper/pull/191
- Update command to use doublequotes for username by @applyACS in https://github.com/spatie/db-dumper/pull/192

### New Contributors

- @dependabot made their first contribution in https://github.com/spatie/db-dumper/pull/186
- @alexmanase made their first contribution in https://github.com/spatie/db-dumper/pull/188
- @applyACS made their first contribution in https://github.com/spatie/db-dumper/pull/192

**Full Changelog**: https://github.com/spatie/db-dumper/compare/3.3.0...3.3.1

## 3.3.0 - 2022-09-01

### What's Changed

- Docs: typo in README.md in column_statics by @Ayoub-Mabrouk in https://github.com/spatie/db-dumper/pull/175
- add excludeTables support for sqlite by @ariaieboy in https://github.com/spatie/db-dumper/pull/177

### New Contributors

- @Ayoub-Mabrouk made their first contribution in https://github.com/spatie/db-dumper/pull/175
- @ariaieboy made their first contribution in https://github.com/spatie/db-dumper/pull/177

**Full Changelog**: https://github.com/spatie/db-dumper/compare/3.2.1...3.3.0

## 3.2.1 - 2022-06-15

### What's Changed

- Update .gitattributes by @angeljqv in https://github.com/spatie/db-dumper/pull/167
- Fix: Replaced single quotes in mongodb dump command in windows env by @malconvsilva in https://github.com/spatie/db-dumper/pull/172

### New Contributors

- @angeljqv made their first contribution in https://github.com/spatie/db-dumper/pull/167
- @malconvsilva made their first contribution in https://github.com/spatie/db-dumper/pull/172

**Full Changelog**: https://github.com/spatie/db-dumper/compare/3.2.0...3.2.1

## 3.2.0 - 2022-03-10

## What's Changed

- Update .gitattributes by @PaolaRuby in https://github.com/spatie/db-dumper/pull/166
- Improve error output for failed dumps

## New Contributors

- @PaolaRuby made their first contribution in https://github.com/spatie/db-dumper/pull/166

**Full Changelog**: https://github.com/spatie/db-dumper/compare/3.1.2...3.2.0

## 3.1.2 - 2022-01-04

## What's Changed

- Escape special characters in PostgreSQL credential entries by @superDuperCyberTechno in https://github.com/spatie/db-dumper/pull/162

## New Contributors

- @superDuperCyberTechno made their first contribution in https://github.com/spatie/db-dumper/pull/162

**Full Changelog**: https://github.com/spatie/db-dumper/compare/3.1.1...3.1.2

## 3.1.1 - 2021-12-21

## What's Changed

- Fixes for vendor by @erikn69 in https://github.com/spatie/db-dumper/pull/159
- Allow symfony v6 by @Nielsvanpach

## New Contributors

- @erikn69 made their first contribution in https://github.com/spatie/db-dumper/pull/159

**Full Changelog**: https://github.com/spatie/db-dumper/compare/3.1.0...3.1.1

## 3.1.0 - 2021-12-08

- Add `includeTables` support for Sqlite

**Full Changelog**: https://github.com/spatie/db-dumper/compare/3.0.1...3.1.0

## 3.0.1 - 2021-04-01

- remove type declaration that causes errors (#151)

## 3.0.0 - 2021-03-31

- require PHP 8+
- drop all PHP 7.x support
- use PHP 8 syntax
- add `Bzip2Compressor` to allow use of the bzip2 compression utility

## 2.21.1 - 2021-02-24

- fix attempt to generate dump over http connection when using socket (#145)

## 2.21.0 - 2021-01-27

- fix: make the process more extensible, avoid touching anything (#141)

## 2.20.0 - 2021-01-26

- revert changes in 2.19.0

## 2.19.0 - 2021-01-25

- fix: add some public functions that allow extensibility (#139)

## 2.18.0 - 2020-10-10

- support PHP 8

## 2.17.0 - 2020-09-10

- add `doNotUseColumnStatistics`

## 2.16.1 - 2020-05-15

- fix using gzip compression on windows (#130)

## 2.16.0 - 2020-04-15

- allow for adding options after the db name (#129)

## 2.15.3 - 2020-01-26

- Fix incomplete credential guard (#126)

## 2.15.2 - 2020-01-16

- Fix sqlite3 dump on Windows

## 2.15.1 - 2019-11-23

- allow symfony 5 components

## 2.15.0 - 2019-11-11

- add `doNotCreateTables` to Postgres driver (#116)

## 2.14.3 - 2019-08-21

- fix memory leak (issue #109)

## 2.14.2 - 2019-06-28

- Determine quotes for windows for MongoDB and PostgreSql dumps (#107)

## 2.14.1 - 2019-05-10

- wrap the dump command in an `if` statement when using compression (#100)
- drop support for PHP 7.2 and lower

## 2.14.0 - 2019-04-17

- add --skip-lock-tables and --quick option (#95)

## 2.13.2 - 2019-03-03

- fix process warnings

## 2.13.1 - 2019-03-01

- remove pipefail operator when compressing dump

## 2.13.0 - 2019-03-01

- add ability to specify all databases as MySQL option

## 2.12.0 - 2018-12-10

- add `doNotCreateTables`

## 2.11.1 - 2018-09-27

- add `useExtension`

## 2.11.0 - 2018-09-26

- add `Compressor`

## 2.10.1 - 2018-08-30

- allow destination paths to have a space character

## 2.10.0 - 2018-04-27

- add support for compressing dumps

## 2.9.0 - 2018-03-05

- add support for setting `--set-gtid-purged`

## 2.8.2 - 2018-01-20

- add support for Symfony 4

## 2.8.1 - 2017-11-24

- fix SQLite dump

## 2.8.0 - 2017-11-13

- add `setAuthenticationDatabase`

## 2.7.4 - 2017-11-07

- fix for dumping a MongoDB without username or password

## 2.7.3 - 2017-09-09

- allow empty passwords for MongoDB dumps

## 2.7.2 - 2017-09-07

- make `--databases` optional

## 2.7.1 - 2017-08-18

- made option passing more flexible by adding `--databases` option to the MySQL dumper

## 2.7.0 - 2017-04-13

- `MongoDb` dumps won't be compressed by default anymore
- add `enableCompression` on `MongoDb`

## 2.6.1 - 2017-04-13

- fix sqlite dumper

## 2.6.0 - 2017-04-13

- add support for MongoDB

## 2.5.1 - 2017-04-07

- prefix excluded tables with database name when dumping a MySql db

## 2.5.0 - 2017-04-05

- add `--default-character-set` option for MySql
- improve the preservation of the used charset when dumping a MySql db

## 2.4.1 - 2016-12-31

- fix bug where custom binary path with spaces on linux would not process correctly

## 2.4.0 - 2016-12-30

- add `skipComments`

## 2.3.0 - 2016-11-21

- add support for SQLite

## 2.1.1 - 2016-11-19

- made a change so the package can be used on Windows

## 2.1.0 - 2016-10-21

- added `getHost`

## 2.0.1 - 2016-09-17

- fix for dump paths with spaces

## 2.0.0 - 2016-09-07

- refactored all classes
- added the ability to add artribrary options

## 1.5.1 - 2016-06-14

- Removed -d flag from pg_dump for compability with pgsql 7.3+

## 1.5.0 - 2016-06-01

- Added `includeTables` and `excludeTables`

## 1.4.0 - 2016-04-27

- Added --single-transaction option to Mysql dump command

## 1.3.0 - 2016-04-03

- Added the ability to use insert when dumping a PostgreSQL db

## 1.2.4 - 2016-03-24

- Added more details about a dump failure in the error message

## 1.2.3 - 2016-03-18

- Fixed an issue where paths containing spaces would cause problems

## 1.2.2 - 2016-03-16

- Added an option to set a timeout

## 1.2.1 - 2016-03-14

- Fixed PostgreSQL dump

## 1.2.0 - 2016-03-13

- Added support for PostgreSQL

## 1.1.0 - 2016-02-21

- Lowered PHP and symfony requirements

## 1.0.4 - 2016-02-14

- Fixed a bug when the backup has failed.

## 1.0.3 - 2016-02-01

- Added missing abstract `getDbName`-method

## 1.0.2 - 2016-02-01

- Added missing abstract `dumpToFile`-method

## 1.0.1 - 2016-01-19

- Fixed typo in `checkIfDumpWasSuccessFul`-method name
- Fixed bug running Process

## 1.0.0 - 2016-01-19

- Initial release
