<?php

use Spatie\DbDumper\Compressors\Bzip2Compressor;
use Spatie\DbDumper\Compressors\GzipCompressor;
use Spatie\DbDumper\Databases\MongoDb;
use Spatie\DbDumper\Exceptions\CannotStartDump;

it('provides a factory method')
    ->expect(MongoDb::create())
    ->toBeInstanceOf(MongoDb::class);

it('will_throw_an_exception_when_no_credentials_are_set')
    ->tap(fn () => MongoDb::create()->dumpToFile('test.gz'))
    ->throws(CannotStartDump::class);

it('can generate a dump command', function () {
    $dumpCommand = MongoDb::create()
        ->setDbName('dbname')
        ->getDumpCommand('dbname.gz');

    expect($dumpCommand)->toEqual('\'mongodump\' --db dbname'
        . ' --archive --host localhost --port 27017 > "dbname.gz"');
});

it('can generate a dump command using a database url', function () {
    $dumpCommand = MongoDb::create()
        ->setDatabaseUrl('monogodb://username:password@localhost:27017/dbname')
        ->getDumpCommand('dbname.gz');

    expect($dumpCommand)->toEqual(
        '\'mongodump\' --db dbname'
            . ' --archive --username \'username\' --password \'password\' --host localhost --port 27017 > "dbname.gz"'
    );
});

it('can generate a dump command with gzip compressor enabled', function () {
    $dumpCommand = MongoDb::create()
        ->setDbName('dbname')
        ->useCompressor(new GzipCompressor())
        ->getDumpCommand('dbname.gz');

    expect($dumpCommand)->toEqual(
        '((((\'mongodump\' --db dbname --archive --host localhost --port 27017; echo $? >&3) | gzip > "dbname.gz") 3>&1) | (read x; exit $x))'
    );
});

it('can generate a dump command with bzip2 compressor enabled', function () {
    $dumpCommand = MongoDb::create()
        ->setDbName('dbname')
        ->useCompressor(new Bzip2Compressor())
        ->getDumpCommand('dbname.bz2');

    expect($dumpCommand)->toEqual(
        '((((\'mongodump\' --db dbname --archive --host localhost --port 27017; echo $? >&3) | bzip2 > "dbname.bz2") 3>&1) | (read x; exit $x))'
    );
});

it('can generate a dump command with absolute path having space and brackets', function () {
    $dumpCommand = MongoDb::create()
        ->setDbName('dbname')
        ->getDumpCommand('/save/to/new (directory)/dbname.gz');

    expect($dumpCommand)->toEqual(
        '\'mongodump\' --db dbname --archive --host localhost --port 27017 > "/save/to/new (directory)/dbname.gz"'
    );
});

it('can generate a dump command with username and password', function () {
    $dumpCommand = MongoDb::create()
        ->setDbName('dbname')
        ->setUserName('username')
        ->setPassword('password')
        ->getDumpCommand('dbname.gz');

    expect($dumpCommand)->toEqual('\'mongodump\' --db dbname --archive'
        . ' --username \'username\' --password \'password\' --host localhost --port 27017 > "dbname.gz"');
});

it('can generate a command with custom host and port', function () {
    $dumpCommand = MongoDb::create()
        ->setDbName('dbname')
        ->setHost('mongodb.test.com')
        ->setPort(27018)
        ->getDumpCommand('dbname.gz');

    expect($dumpCommand)->toEqual('\'mongodump\' --db dbname --archive'
        . ' --host mongodb.test.com --port 27018 > "dbname.gz"');
});

it('can generate a backup command for a single collection', function () {
    $dumpCommand = MongoDb::create()
        ->setDbName('dbname')
        ->setCollection('mycollection')
        ->getDumpCommand('dbname.gz');

    expect($dumpCommand)->toEqual('\'mongodump\' --db dbname --archive'
        . ' --host localhost --port 27017 --collection mycollection > "dbname.gz"');
});

it('can generate a dump command with custom binary path', function () {
    $dumpCommand = MongoDb::create()
        ->setDbName('dbname')
        ->setDumpBinaryPath('/custom/directory')
        ->getDumpCommand('dbname.gz');

    expect($dumpCommand)->toEqual(
        '\'/custom/directory/mongodump\' --db dbname --archive'
            . ' --host localhost --port 27017 > "dbname.gz"'
    );
});

it('can generate a dump command with authentication database', function () {
    $dumpCommand = MongoDb::create()
        ->setDbName('dbname')
        ->setAuthenticationDatabase('admin')
        ->getDumpCommand('dbname.gz');

    expect($dumpCommand)->toEqual(
        '\'mongodump\' --db dbname --archive'
            . ' --host localhost --port 27017 --authenticationDatabase admin > "dbname.gz"'
    );
});
