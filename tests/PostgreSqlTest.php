<?php

use Spatie\DbDumper\Compressors\Bzip2Compressor;
use Spatie\DbDumper\Compressors\GzipCompressor;
use Spatie\DbDumper\Databases\PostgreSql;
use Spatie\DbDumper\Exceptions\CannotSetParameter;
use Spatie\DbDumper\Exceptions\CannotStartDump;

it('provides a factory method')
    ->expect(PostgreSql::create())
    ->toBeInstanceOf(PostgreSql::class);

it('will throw an exception when no credentials are set')
    ->tap(fn () => PostgreSql::create()->dumpToFile('test.sql'))
    ->throws(CannotStartDump::class);

it('can generate a dump command', function () {
    $dumpCommand = PostgreSql::create()
        ->setDbName('dbname')
        ->setUserName('username')
        ->setPassword('password')
        ->getDumpCommand('dump.sql');

    expect($dumpCommand)->toEqual('\'pg_dump\' -U "username" -h localhost -p 5432 > "dump.sql"');
});

it('can generate a dump command using a database url', function () {
    $dumpCommand = Postgresql::create()
        ->setDatabaseUrl('postgres://username:password@hostname:5432/dbname')
        ->getDumpCommand('dump.sql');

    expect($dumpCommand)->toEqual('\'pg_dump\' -U "username" -h hostname -p 5432 > "dump.sql"');
});

it('can generate a dump command with gzip compressor enabled', function () {
    $dumpCommand = PostgreSql::create()
        ->setDbName('dbname')
        ->setUserName('username')
        ->setPassword('password')
        ->useCompressor(new GzipCompressor())
        ->getDumpCommand('dump.sql');

    expect($dumpCommand)->toEqual(
        '((((\'pg_dump\' -U "username" -h localhost -p 5432; echo $? >&3) | gzip > "dump.sql") 3>&1) | (read x; exit $x))'
    );
});

it('can generate a dump command with bzip2 compressor enabled', function () {
    $dumpCommand = PostgreSql::create()
        ->setDbName('dbname')
        ->setUserName('username')
        ->setPassword('password')
        ->useCompressor(new Bzip2Compressor())
        ->getDumpCommand('dump.sql');

    $expected = '((((\'pg_dump\' -U "username" -h localhost -p 5432; echo $? >&3) | bzip2 > "dump.sql") 3>&1) | (read x; exit $x))';

    expect($dumpCommand)->toEqual($expected);
});

it('can generate a dump command with absolute path having space and brackets', function () {
    $dumpCommand = PostgreSql::create()
        ->setDbName('dbname')
        ->setUserName('username')
        ->setPassword('password')
        ->getDumpCommand('/save/to/new (directory)/dump.sql');

    expect($dumpCommand)->toEqual(
        '\'pg_dump\' -U "username" -h localhost -p 5432 > "/save/to/new (directory)/dump.sql"'
    );
});

it('can generate a dump command with using inserts', function () {
    $dumpCommand = PostgreSql::create()
        ->setDbName('dbname')
        ->setUserName('username')
        ->setPassword('password')
        ->useInserts()
        ->getDumpCommand('dump.sql');

    expect($dumpCommand)->toEqual(
        '\'pg_dump\' -U "username" -h localhost -p 5432 --inserts > "dump.sql"'
    );
});

it('can generate a dump command with a custom port', function () {
    $dumpCommand = PostgreSql::create()
        ->setDbName('dbname')
        ->setUserName('username')
        ->setPassword('password')
        ->setPort(1234)
        ->getDumpCommand('dump.sql');

    expect($dumpCommand)->toEqual('\'pg_dump\' -U "username" -h localhost -p 1234 > "dump.sql"');
});

it('can generate a dump command with custom binary path', function () {
    $dumpCommand = PostgreSql::create()
        ->setDbName('dbname')
        ->setUserName('username')
        ->setPassword('password')
        ->setDumpBinaryPath('/custom/directory')
        ->getDumpCommand('dump.sql');

    expect($dumpCommand)->toEqual('\'/custom/directory/pg_dump\' -U "username" -h localhost -p 5432 > "dump.sql"');
});

it('can generate a dump command with a custom socket', function () {
    $dumpCommand = PostgreSql::create()
        ->setDbName('dbname')
        ->setUserName('username')
        ->setPassword('password')
        ->setSocket('/var/socket.1234')
        ->getDumpCommand('dump.sql');

    expect($dumpCommand)->toEqual('\'pg_dump\' -U "username" -h /var/socket.1234 -p 5432 > "dump.sql"');
});

it('can generate a dump command for specific tables as array', function () {
    $dumpCommand = PostgreSql::create()
        ->setDbName('dbname')
        ->setUserName('username')
        ->setPassword('password')
        ->includeTables(['tb1', 'tb2', 'tb3'])
        ->getDumpCommand('dump.sql', 'credentials.txt');

    expect($dumpCommand)->toEqual('\'pg_dump\' -U "username" -h localhost -p 5432 -t tb1 -t tb2 -t tb3 > "dump.sql"');
});

it('can generate a dump command for specific tables as string', function () {
    $dumpCommand = PostgreSql::create()
        ->setDbName('dbname')
        ->setUserName('username')
        ->setPassword('password')
        ->includeTables('tb1, tb2, tb3')
        ->getDumpCommand('dump.sql', 'credentials.txt');

    expect($dumpCommand)->toEqual('\'pg_dump\' -U "username" -h localhost -p 5432 -t tb1 -t tb2 -t tb3 > "dump.sql"');
});

it('will throw an exception when setting exclude tables after setting tables', function () {
    PostgreSql::create()
        ->setDbName('dbname')
        ->setUserName('username')
        ->setPassword('password')
        ->includeTables('tb1, tb2, tb3')
        ->excludeTables('tb4, tb5, tb6');
})->throws(CannotSetParameter::class);

it('can generate a dump command excluding tables as array', function () {
    $dumpCommand = PostgreSql::create()
        ->setDbName('dbname')
        ->setUserName('username')
        ->setPassword('password')
        ->excludeTables(['tb1', 'tb2', 'tb3'])
        ->getDumpCommand('dump.sql', 'credentials.txt');

    expect($dumpCommand)->toEqual(
        '\'pg_dump\' -U "username" -h localhost -p 5432 -T tb1 -T tb2 -T tb3 > "dump.sql"'
    );
});

it('can generate a dump command excluding tables as string', function () {
    $dumpCommand = PostgreSql::create()
        ->setDbName('dbname')
        ->setUserName('username')
        ->setPassword('password')
        ->excludeTables('tb1, tb2, tb3')
        ->getDumpCommand('dump.sql', 'credentials.txt');

    expect($dumpCommand)->toEqual(
        '\'pg_dump\' -U "username" -h localhost -p 5432 -T tb1 -T tb2 -T tb3 > "dump.sql"'
    );
});

it('will throw an exception when setting tables after setting exclude tables', function () {
    PostgreSql::create()
        ->setDbName('dbname')
        ->setUserName('username')
        ->setPassword('password')
        ->excludeTables('tb1, tb2, tb3')
        ->includeTables('tb4, tb5, tb6');
})->throws(CannotSetParameter::class);

it('can generate the contents of a credentials file', function () {
    $credentialsFileContent = PostgreSql::create()
        ->setDbName('dbname')
        ->setUserName('username')
        ->setPassword('password')
        ->setHost('hostname')
        ->setPort(5432)
        ->getContentsOfCredentialsFile();

    expect($credentialsFileContent)->toEqual('hostname:5432:dbname:username:password');
});

it('can get the name of the db', function () {
    $dbName = 'testName';

    $dbDumper = PostgreSql::create()->setDbName($dbName);

    expect($dbDumper->getDbName())->toEqual($dbName);
});

it('can add an extra option', function () {
    $dumpCommand = PostgreSql::create()
        ->setDbName('dbname')
        ->setUserName('username')
        ->setPassword('password')
        ->addExtraOption('-something-else')
        ->getDumpCommand('dump.sql');

    expect($dumpCommand)->toEqual(
        '\'pg_dump\' -U "username" -h localhost -p 5432 -something-else > "dump.sql"'
    );
});

it('can get the host', function () {
    $dumper = PostgreSql::create()->setHost('myHost');

    expect($dumper->getHost())->toEqual('myHost');
});

it('can generate a dump command with no create info', function () {
    $dumpCommand = PostgreSql::create()
        ->setDbName('dbname')
        ->setUserName('username')
        ->setPassword('password')
        ->doNotCreateTables()
        ->getDumpCommand('dump.sql');

    expect($dumpCommand)->toEqual('\'pg_dump\' -U "username" -h localhost -p 5432 --data-only > "dump.sql"');
});
