<?php

use function PHPUnit\Framework\assertFileExists;
use function PHPUnit\Framework\assertNotEquals;

use Spatie\DbDumper\Compressors\Bzip2Compressor;
use Spatie\DbDumper\Compressors\GzipCompressor;
use Spatie\DbDumper\Databases\Sqlite;

it('provides a factory method')
    ->expect(Sqlite::create())
    ->toBeInstanceOf(Sqlite::class);

it('can generate a dump command', function () {
    $dumpCommand = Sqlite::create()
        ->setDbName('dbname.sqlite')
        ->getDumpCommand('dump.sql');

    $expected = "echo 'BEGIN;\n.dump' | 'sqlite3' --bail 'dbname.sqlite' > \"dump.sql\"";

    expect($dumpCommand)->toEqual($expected);
});

it('can generate a dump command using a database url containing an absolute path', function () {
    $dumpCommand = Sqlite::create()
        ->setDatabaseUrl('sqlite:///path/to/dbname.sqlite')
        ->getDumpCommand('dump.sql');

    expect($dumpCommand)->toEqual(
        "echo 'BEGIN;\n.dump' | 'sqlite3' --bail '/path/to/dbname.sqlite' > \"dump.sql\""
    );
});

it('can generate a dump command using a database url containing a relative path', function () {
    $dumpCommand = Sqlite::create()
        ->setDatabaseUrl('sqlite:dbname.sqlite')
        ->getDumpCommand('dump.sql');

    expect($dumpCommand)->toEqual(
        "echo 'BEGIN;\n.dump' | 'sqlite3' --bail 'dbname.sqlite' > \"dump.sql\""
    );
});

it('can generate a dump command with gzip compressor enabled', function () {
    $dumpCommand = Sqlite::create()
        ->setDbName('dbname.sqlite')
        ->useCompressor(new GzipCompressor())
        ->getDumpCommand('dump.sql');

    expect($dumpCommand)->toEqual(
        '((((echo \'BEGIN;
.dump\' | \'sqlite3\' --bail \'dbname.sqlite\'; echo $? >&3) | gzip > "dump.sql") 3>&1) | (read x; exit $x))'
    );
});

it('can generate a dump command with bzip2 compressor enabled', function () {
    $dumpCommand = Sqlite::create()
        ->setDbName('dbname.sqlite')
        ->useCompressor(new Bzip2Compressor())
        ->getDumpCommand('dump.sql');

    expect($dumpCommand)->toEqual(
        '((((echo \'BEGIN;
.dump\' | \'sqlite3\' --bail \'dbname.sqlite\'; echo $? >&3) | bzip2 > "dump.sql") 3>&1) | (read x; exit $x))'
    );
});

it('can generate a dump command with only specific tables included', function () {
    $dumpCommand = Sqlite::create()
        ->setDbName('dbname.sqlite')
        ->includeTables(['users', 'posts'])
        ->getDumpCommand('dump.sql');

    expect($dumpCommand)->toEqual(
        "echo 'BEGIN;\n.dump users posts' | 'sqlite3' --bail 'dbname.sqlite' > \"dump.sql\""
    );
});

it('can generate a dump command without excluded tables included', function () {
    $dbPath = __DIR__ . '/stubs/testDB.sqlite';
    $dumpCommand = Sqlite::create()
        ->setDbName($dbPath)
        ->excludeTables(['tb2', 'tb3'])
        ->getDumpCommand('dump.sql');

    $expected = "echo 'BEGIN;\n.dump tb1 tb4' | 'sqlite3' --bail '{$dbPath}' > \"dump.sql\"";

    expect($dumpCommand)->toEqual($expected);
});

it('can return current db table list', function () {
    $dbPath = __DIR__ . '/stubs/testDB.sqlite';
    $dumpCommand = Sqlite::create()
        ->setDbName($dbPath)
        ->getDbTables();

    expect($dumpCommand)->toEqual(
        ['tb1', 'tb2', 'tb3', 'tb4']
    );
});

it('can generate a dump command with absolute paths', function () {
    $dumpCommand = Sqlite::create()
        ->setDbName('/path/to/dbname.sqlite')
        ->setDumpBinaryPath('/usr/bin')
        ->getDumpCommand('/save/to/dump.sql');

    expect($dumpCommand)->toEqual(
        "echo 'BEGIN;\n.dump' | '/usr/bin/sqlite3' --bail '/path/to/dbname.sqlite' > \"/save/to/dump.sql\""
    );
});

it('can generate a dump command with absolute paths having space and brackets', function () {
    $dumpCommand = Sqlite::create()
        ->setDbName('/path/to/dbname.sqlite')
        ->setDumpBinaryPath('/usr/bin')
        ->getDumpCommand('/save/to/new (directory)/dump.sql');

    expect($dumpCommand)->toEqual(
        "echo 'BEGIN;\n.dump' | '/usr/bin/sqlite3' --bail '/path/to/dbname.sqlite' > \"/save/to/new (directory)/dump.sql\""
    );
});

it('successfully creates a backup', function () {
    $dbPath = __DIR__ . '/stubs/database.sqlite';
    $dbBackupPath = __DIR__ . '/temp/backup.sql';

    Sqlite::create()
        ->setDbName($dbPath)
        ->useCompressor(new GzipCompressor())
        ->dumpToFile($dbBackupPath);

    assertFileExists($dbBackupPath);
    assertNotEquals(0, filesize($dbBackupPath), 'Sqlite dump cannot be empty');
});

it('does not block writers while dumping', function () {
    $dbPath = __DIR__ . '/temp/live.sqlite';
    $fifo = __DIR__ . '/temp/dump.fifo';
    $cleanUp = fn () => array_map('unlink', array_filter([$fifo, $dbPath, "{$dbPath}-wal", "{$dbPath}-shm"], 'file_exists'));
    $cleanUp();

    $pdo = new PDO("sqlite:{$dbPath}");
    $pdo->exec('PRAGMA journal_mode = wal');
    $pdo->exec('CREATE TABLE items (payload TEXT)');
    $pdo->exec('WITH RECURSIVE n(i) AS (SELECT 1 UNION ALL SELECT i + 1 FROM n WHERE i < 5000) INSERT INTO items SELECT hex(randomblob(64)) FROM n');
    $pdo->exec('PRAGMA busy_timeout = 0');

    // Nobody drains the pipe yet, so sqlite3 stalls mid-dump with its
    // transaction open once the pipe buffer is full.
    posix_mkfifo($fifo, 0600);
    $process = Sqlite::create()->setDbName($dbPath)->getProcess($fifo);
    $process->start();
    $reader = fopen($fifo, 'r');

    try {
        fread($reader, 1);

        $pdo->exec("INSERT INTO items (payload) VALUES ('written during the dump')");

        stream_set_blocking($reader, false);
        $dump = '';
        while ($process->isRunning()) {
            $dump .= fread($reader, 65536);
        }
        $dump .= stream_get_contents($reader);
    } finally {
        fclose($reader);
        $process->stop();
        $pdo = null;
        $cleanUp();
    }

    expect($process->isSuccessful())->toBeTrue()
        ->and(substr_count($dump, 'INSERT INTO'))->toBe(5000)
        ->and($dump)->not->toContain('written during the dump');
})->skipOnWindows();
