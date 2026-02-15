<?php

use Spatie\DbDumper\Compressors\Bzip2Compressor;
use Spatie\DbDumper\Compressors\GzipCompressor;
use Spatie\DbDumper\Databases\MySql;
use Spatie\DbDumper\Exceptions\CannotSetParameter;
use Spatie\DbDumper\Exceptions\CannotStartDump;

it('provides a factory method')
    ->expect(MySql::create())
    ->toBeInstanceOf(MySql::class);

it('will throw an exception when no credentials are set', function () {
    MySql::create()->dumpToFile('test.sql');
})->throws(CannotStartDump::class);

it('can generate a dump command', function () {
    $dumpCommand = MySql::create()
        ->setDbName('dbname')
        ->setUserName('username')
        ->setPassword('password')
        ->getDumpCommand('dump.sql', 'credentials.txt');

    expect($dumpCommand)->toEqual(
        '\'mysqldump\' --defaults-extra-file="credentials.txt" --skip-comments --extended-insert dbname > "dump.sql"'
    );
});

it('can generate a dump command using a database url', function () {
    $dumpCommand = Mysql::create()
        ->setDatabaseUrl('mysql://username:password@hostname:3306/dbname')
        ->getDumpCommand('dump.sql', 'credentials.txt');

    expect($dumpCommand)->toEqual(
        '\'mysqldump\' --defaults-extra-file="credentials.txt" --skip-comments --extended-insert dbname > "dump.sql"'
    );
});

it('can generate a dump command with columnstatistics', function () {
    $dumpCommand = MySql::create()
        ->setDbName('dbname')
        ->setUserName('username')
        ->setPassword('password')
        ->doNotUseColumnStatistics()
        ->getDumpCommand('dump.sql', 'credentials.txt');

    expect($dumpCommand)->toEqual(
        '\'mysqldump\' --defaults-extra-file="credentials.txt" --skip-comments --extended-insert --column-statistics=0 dbname > "dump.sql"'
    );
});

it('can generate a dump command with gzip compressor enabled', function () {
    $dumpCommand = MySql::create()
        ->setDbName('dbname')
        ->setUserName('username')
        ->setPassword('password')
        ->useCompressor(new GzipCompressor())
        ->getDumpCommand('dump.sql', 'credentials.txt');

    expect($dumpCommand)->toEqual(
        '((((\'mysqldump\' --defaults-extra-file="credentials.txt" --skip-comments --extended-insert dbname; echo $? >&3) | gzip > "dump.sql") 3>&1) | (read x; exit $x))'
    );
});

it('can generate a dump command with bzip2 compressor enabled', function () {
    $dumpCommand = MySql::create()
        ->setDbName('dbname')
        ->setUserName('username')
        ->setPassword('password')
        ->useCompressor(new Bzip2Compressor())
        ->getDumpCommand('dump.sql', 'credentials.txt');

    expect($dumpCommand)->toEqual(
        '((((\'mysqldump\' --defaults-extra-file="credentials.txt" --skip-comments --extended-insert dbname; echo $? >&3) | bzip2 > "dump.sql") 3>&1) | (read x; exit $x))'
    );
});

it('can generate a dump command with absolute path having space and brackets', function () {
    $dumpCommand = MySql::create()
        ->setDbName('dbname')
        ->setUserName('username')
        ->setPassword('password')
        ->getDumpCommand('/save/to/new (directory)/dump.sql', 'credentials.txt');

    expect($dumpCommand)->toEqual(
        '\'mysqldump\' --defaults-extra-file="credentials.txt" --skip-comments --extended-insert dbname > "/save/to/new (directory)/dump.sql"'
    );
});

it('can generate a dump command without using comments', function () {
    $dumpCommand = MySql::create()
        ->setDbName('dbname')
        ->setUserName('username')
        ->setPassword('password')
        ->doNotSkipComments()
        ->getDumpCommand('dump.sql', 'credentials.txt');

    expect($dumpCommand)->toEqual(
        '\'mysqldump\' --defaults-extra-file="credentials.txt" --extended-insert dbname > "dump.sql"'
    );
});

it('can generate a dump command without using extended inserts', function () {
    $dumpCommand = MySql::create()
        ->setDbName('dbname')
        ->setUserName('username')
        ->setPassword('password')
        ->doNotUseExtendedInserts()
        ->getDumpCommand('dump.sql', 'credentials.txt');

    expect($dumpCommand)->toEqual(
        '\'mysqldump\' --defaults-extra-file="credentials.txt" --skip-comments --skip-extended-insert dbname > "dump.sql"'
    );
});

it('can generate a dump command with custom binary path', function () {
    $dumpCommand = MySql::create()
        ->setDbName('dbname')
        ->setUserName('username')
        ->setPassword('password')
        ->setDumpBinaryPath('/custom/directory')
        ->getDumpCommand('dump.sql', 'credentials.txt');

    expect($dumpCommand)->toEqual(
        '\'/custom/directory/mysqldump\' --defaults-extra-file="credentials.txt" --skip-comments --extended-insert dbname > "dump.sql"'
    );
});

it('can generate a dump command without using extending inserts', function () {
    $dumpCommand = MySql::create()
        ->setDbName('dbname')
        ->setUserName('username')
        ->setPassword('password')
        ->doNotUseExtendedInserts()
        ->getDumpCommand('dump.sql', 'credentials.txt');

    expect($dumpCommand)->toEqual(
        '\'mysqldump\' --defaults-extra-file="credentials.txt" --skip-comments --skip-extended-insert dbname > "dump.sql"'
    );
});

it('can generate a dump command using single transaction', function () {
    $dumpCommand = MySql::create()
        ->setDbName('dbname')
        ->setUserName('username')
        ->setPassword('password')
        ->useSingleTransaction()
        ->getDumpCommand('dump.sql', 'credentials.txt');

    expect($dumpCommand)->toEqual(
        '\'mysqldump\' --defaults-extra-file="credentials.txt" --skip-comments --extended-insert --single-transaction dbname > "dump.sql"'
    );
});

it('can generate a dump command using skip lock tables', function () {
    $dumpCommand = MySql::create()
        ->setDbName('dbname')
        ->setUserName('username')
        ->setPassword('password')
        ->skipLockTables()
        ->getDumpCommand('dump.sql', 'credentials.txt');

    expect($dumpCommand)->toEqual(
        '\'mysqldump\' --defaults-extra-file="credentials.txt" --skip-comments --extended-insert --skip-lock-tables dbname > "dump.sql"'
    );
});

it('can generate a dump command using quick', function () {
    $dumpCommand = MySql::create()
        ->setDbName('dbname')
        ->setUserName('username')
        ->setPassword('password')
        ->useQuick()
        ->getDumpCommand('dump.sql', 'credentials.txt');

    expect($dumpCommand)->toEqual(
        '\'mysqldump\' --defaults-extra-file="credentials.txt" --skip-comments --extended-insert --quick dbname > "dump.sql"'
    );
});

it('can generate a dump command with a custom socket', function () {
    $dumpCommand = MySql::create()
        ->setDbName('dbname')
        ->setUserName('username')
        ->setPassword('password')
        ->setSocket(1234)
        ->getDumpCommand('dump.sql', 'credentials.txt');

    expect($dumpCommand)->toEqual(
        '\'mysqldump\' --defaults-extra-file="credentials.txt" --skip-comments --extended-insert --socket=1234 dbname > "dump.sql"'
    );
});

it('can generate a dump command for specific tables as array', function () {
    $dumpCommand = MySql::create()
        ->setDbName('dbname')
        ->setUserName('username')
        ->setPassword('password')
        ->includeTables(['tb1', 'tb2', 'tb3'])
        ->getDumpCommand('dump.sql', 'credentials.txt');

    expect($dumpCommand)->toEqual(
        '\'mysqldump\' --defaults-extra-file="credentials.txt" --skip-comments --extended-insert dbname --tables tb1 tb2 tb3 > "dump.sql"'
    );
});

it('can generate a dump command skipping auto increment values', function () {
    $dumpCommand = MySql::create()
        ->setDbName('dbname')
        ->setUserName('username')
        ->setPassword('password')
        ->skipAutoIncrement()
        ->getDumpCommand('dump.sql', 'credentials.txt');

    expect($dumpCommand)->toContain("sed 's/ AUTO_INCREMENT=[0-9]*\\b//'");
});

it('can generate a dump command not skipping auto increment values', function () {
    $dumpCommand = MySql::create()
        ->setDbName('dbname')
        ->setUserName('username')
        ->setPassword('password')
        ->doNotSkipAutoIncrement()
        ->getDumpCommand('dump.sql', 'credentials.txt');

    expect($dumpCommand)->not->toContain("sed 's/ AUTO_INCREMENT=[0-9]*\\b//'");
});

it('can generate a dump command for specific tables as string', function () {
    $dumpCommand = MySql::create()
        ->setDbName('dbname')
        ->setUserName('username')
        ->setPassword('password')
        ->includeTables('tb1 tb2 tb3')
        ->getDumpCommand('dump.sql', 'credentials.txt');

    expect($dumpCommand)->toEqual(
        '\'mysqldump\' --defaults-extra-file="credentials.txt" --skip-comments --extended-insert dbname --tables tb1 tb2 tb3 > "dump.sql"'
    );
});

it('will throw an exception when setting exclude tables after setting tables', function () {
    MySql::create()
        ->setDbName('dbname')
        ->setUserName('username')
        ->setPassword('password')
        ->includeTables('tb1 tb2 tb3')
        ->excludeTables('tb4 tb5 tb6');
})->throws(CannotSetParameter::class);

it('can generate a dump command excluding tables as array', function () {
    $dumpCommand = MySql::create()
        ->setDbName('dbname')
        ->setUserName('username')
        ->setPassword('password')
        ->excludeTables(['tb1', 'tb2', 'tb3'])
        ->getDumpCommand('dump.sql', 'credentials.txt');

    expect($dumpCommand)->toEqual(
        '\'mysqldump\' --defaults-extra-file="credentials.txt" --skip-comments --extended-insert ' .
            '--ignore-table=dbname.tb1 --ignore-table=dbname.tb2 --ignore-table=dbname.tb3 dbname > "dump.sql"'
    );
});

it('can generate a dump command excluding tables as string', function () {
    $dumpCommand = MySql::create()
        ->setDbName('dbname')
        ->setUserName('username')
        ->setPassword('password')
        ->excludeTables('tb1, tb2, tb3')
        ->getDumpCommand('dump.sql', 'credentials.txt');

    expect($dumpCommand)->toEqual(
        '\'mysqldump\' --defaults-extra-file="credentials.txt" --skip-comments --extended-insert ' .
            '--ignore-table=dbname.tb1 --ignore-table=dbname.tb2 --ignore-table=dbname.tb3 dbname > "dump.sql"'
    );
});

it('will throw an exception when setting tables after setting exclude tables', function () {
    MySql::create()
        ->setDbName('dbname')
        ->setUserName('username')
        ->setPassword('password')
        ->excludeTables('tb1 tb2 tb3')
        ->includeTables('tb4 tb5 tb6');
})->throws(CannotSetParameter::class);

it('can generate the contents of a credentials file with a socket connetion', function () {
    $credentialsFileContent = MySql::create()
        ->setDbName('dbname')
        ->setUserName('username')
        ->setPassword('password')
        ->setHost('hostname')
        ->setSocket(1234)
        ->getContentsOfCredentialsFile();

    expect($credentialsFileContent)->toEqual(
        '[client]' . PHP_EOL . "user = 'username'" . PHP_EOL . "password = 'password'" . PHP_EOL . "port = '3306'"
    );
});

it('can generate the contents of a credentials file with a http connection', function () {
    $credentialsFileContent = MySql::create()
        ->setDbName('dbname')
        ->setUserName('username')
        ->setPassword('password')
        ->setHost('hostname')
        ->getContentsOfCredentialsFile();

    expect($credentialsFileContent)->toEqual(
        '[client]' . PHP_EOL . "user = 'username'" . PHP_EOL . "password = 'password'" . PHP_EOL . "port = '3306'" . PHP_EOL . "host = 'hostname'"
    );
});

it('can get the name of the db', function () {
    $dbName = 'testName';

    $dbDumper = MySql::create()->setDbName($dbName);

    expect($dbDumper->getDbName())->toEqual($dbName);
});

it('can add extra options', function () {
    $dumpCommand = MySql::create()
        ->setDbName('dbname')
        ->setUserName('username')
        ->setPassword('password')
        ->addExtraOption('--extra-option')
        ->addExtraOption('--another-extra-option="value"')
        ->getDumpCommand('dump.sql', 'credentials.txt');

    expect($dumpCommand)->toEqual(
        '\'mysqldump\' --defaults-extra-file="credentials.txt" --skip-comments --extended-insert --extra-option --another-extra-option="value" dbname > "dump.sql"'
    );
});

it('can add extra options after db name', function () {
    $dumpCommand = MySql::create()
        ->setDbName('dbname')
        ->setUserName('username')
        ->setPassword('password')
        ->addExtraOption('--extra-option')
        ->addExtraOptionAfterDbName('--another-extra-option="value"')
        ->getDumpCommand('dump.sql', 'credentials.txt');

    expect($dumpCommand)->toEqual(
        '\'mysqldump\' --defaults-extra-file="credentials.txt" --skip-comments --extended-insert --extra-option dbname --another-extra-option="value" > "dump.sql"'
    );
});

it('can get the host', function () {
    $dumper = MySql::create()->setHost('myHost');

    expect($dumper->getHost())->toEqual('myHost');
});

it('can set db name as an extra options', function () {
    $dumpCommand = MySql::create()
        ->setUserName('username')
        ->setPassword('password')
        ->addExtraOption('--extra-option')
        ->addExtraOption('--another-extra-option="value"')
        ->addExtraOption('--databases dbname')
        ->getDumpCommand('dump.sql', 'credentials.txt');

    expect($dumpCommand)->toEqual('\'mysqldump\' --defaults-extra-file="credentials.txt" --skip-comments --extended-insert --extra-option --another-extra-option="value" --databases dbname > "dump.sql"');
});

it('can get the name of the db when dbname was set as an extra option', function () {
    $dbName = 'testName';

    $dbDumper = MySql::create()->addExtraOption("--databases {$dbName}");

    expect($dbDumper->getDbName())->toEqual($dbName);
});

it('can get the name of the db when dbname was overriden as an extra option', function () {
    $dbName = 'testName';
    $overridenDbName = 'otherName';

    $dbDumper = MySql::create()->setDbName($dbName)->addExtraOption("--databases {$overridenDbName}");

    expect($dbDumper->getDbName())->toEqual($overridenDbName);
});

it('can get the name of the db when all databases was set as an extra option', function () {
    $dumpCommand = MySql::create()
        ->setUserName('username')
        ->setPassword('password')
        ->addExtraOption('--all-databases')
        ->getDumpCommand('dump.sql', 'credentials.txt');

    expect($dumpCommand)->toEqual(
        '\'mysqldump\' --defaults-extra-file="credentials.txt" --skip-comments --extended-insert --all-databases > "dump.sql"'
    );
});

it('can generate a dump command excluding tables as array when dbname was set as an extra option', function () {
    $dumpCommand = MySql::create()
        ->setUserName('username')
        ->setPassword('password')
        ->addExtraOption('--databases dbname')
        ->excludeTables(['tb1', 'tb2', 'tb3'])
        ->getDumpCommand('dump.sql', 'credentials.txt');

    expect($dumpCommand)->toEqual(
        '\'mysqldump\' --defaults-extra-file="credentials.txt" --skip-comments --extended-insert ' .
            '--ignore-table=dbname.tb1 --ignore-table=dbname.tb2 --ignore-table=dbname.tb3 --databases dbname > "dump.sql"'
    );
});

it('can generate a dump command excluding tables as string when dbname was set as an extra option', function () {
    $dumpCommand = MySql::create()
        ->setUserName('username')
        ->setPassword('password')
        ->addExtraOption('--databases dbname')
        ->excludeTables('tb1, tb2, tb3')
        ->getDumpCommand('dump.sql', 'credentials.txt');

    expect($dumpCommand)->toEqual(
        '\'mysqldump\' --defaults-extra-file="credentials.txt" --skip-comments --extended-insert ' .
            '--ignore-table=dbname.tb1 --ignore-table=dbname.tb2 --ignore-table=dbname.tb3 --databases dbname > "dump.sql"'
    );
});

it('can generate a dump command with set gtid purged', function () {
    $dumpCommand = MySql::create()
        ->setDbName('dbname')
        ->setUserName('username')
        ->setPassword('password')
        ->setGtidPurged('OFF')
        ->getDumpCommand('dump.sql', 'credentials.txt');

    expect($dumpCommand)->toEqual(
        '\'mysqldump\' --defaults-extra-file="credentials.txt" --skip-comments --extended-insert --set-gtid-purged=OFF dbname > "dump.sql"'
    );
});

it('can generate a dump command with no create info', function () {
    $dumpCommand = MySQL::create()
        ->setDbName('dbname')
        ->setUserName('username')
        ->setPassword('password')
        ->doNotCreateTables()
        ->getDumpCommand('dump.sql', 'credentials.txt');

    expect($dumpCommand)->toEqual(
        '\'mysqldump\' --defaults-extra-file="credentials.txt" --no-create-info --skip-comments --extended-insert dbname > "dump.sql"'
    );
});


it('can generate a dump command with no data', function () {
    $dumpCommand = MySQL::create()
        ->setDbName('dbname')
        ->setUserName('username')
        ->setPassword('password')
        ->doNotDumpData()
        ->getDumpCommand('dump.sql', 'credentials.txt');

    expect($dumpCommand)->toEqual(
        '\'mysqldump\' --defaults-extra-file="credentials.txt" --no-data --skip-comments --extended-insert dbname > "dump.sql"'
    );
});

// We already implicitly test that by default append mode is off, with the expected command strings in the other tests
it('can be set to use append mode', function () {
    $dumpCommand = MySql::create()
        ->setDbName('dbname')
        ->setUserName('username')
        ->setPassword('password')
        ->useAppendMode()
        ->getDumpCommand('dump.sql', 'credentials.txt');

    expect($dumpCommand)->toEqual(
        '\'mysqldump\' --defaults-extra-file="credentials.txt" --skip-comments --extended-insert dbname >> "dump.sql"'
    );
});

it('will throw an exception when using Bzip2Compressor-mode while GzipCompressor is already used.', function () {
    MySql::create()
        ->setDbName('dbname')
        ->setUserName('username')
        ->setPassword('password')
        ->useCompressor(new Bzip2Compressor())
        ->useAppendMode();
})->throws(CannotSetParameter::class);

it('will throw an exception when using Bzip2Compressor while append-mode is already used.', function () {
    MySql::create()
        ->setDbName('dbname')
        ->setUserName('username')
        ->setPassword('password')
        ->useAppendMode()
        ->useCompressor(new Bzip2Compressor());
})->throws(CannotSetParameter::class);

it('will throw an exception when using append-mode while GzipCompressor is already used.', function () {
    MySql::create()
        ->setDbName('dbname')
        ->setUserName('username')
        ->setPassword('password')
        ->useCompressor(new GzipCompressor())
        ->useAppendMode();
})->throws(CannotSetParameter::class);

it('will throw an exception when using GzipCompressor while append-mode is already used.', function () {
    MySql::create()
        ->setDbName('dbname')
        ->setUserName('username')
        ->setPassword('password')
        ->useAppendMode()
        ->useCompressor(new GzipCompressor());
})->throws(CannotSetParameter::class);

it('can generate a dump command with routines included', function () {
    $dumpCommand = MySql::create()
        ->setDbName('dbname')
        ->setUserName('username')
        ->setPassword('password')
        ->includeRoutines()
        ->getDumpCommand('dump.sql', 'credentials.txt');

    expect($dumpCommand)->toEqual(
        '\'mysqldump\' --defaults-extra-file="credentials.txt" --skip-comments --extended-insert --routines dbname > "dump.sql"'
    );
});

it('defaults to ssl-mode=DISABLED when skipping ssl', function () {
    $credentialsFileContent = MySql::create()
        ->setDbName('dbname')
        ->setUserName('username')
        ->setPassword('password')
        ->setHost('hostname')
        ->setSkipSsl()
        ->getContentsOfCredentialsFile();

    expect($credentialsFileContent)->toContain('ssl-mode=DISABLED');
});
