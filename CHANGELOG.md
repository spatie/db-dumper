# Changelog

All notable changes to `db-dumper` will be documented in this file

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

-  Removed -d flag from pg_dump for compability with pgsql 7.3+

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
