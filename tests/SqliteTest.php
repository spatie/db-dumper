<?php

namespace Spatie\DbDumper\Test;

use PHPUnit\Framework\TestCase;
use Spatie\DbDumper\Databases\Sqlite;

class SqliteTest extends TestCase
{
    /** @test */
    public function it_provides_a_factory_method()
    {
        $this->assertInstanceOf(Sqlite::class, Sqlite::create());
    }

    /** @test */
    public function it_can_generate_a_dump_command()
    {
        $dumpCommand = Sqlite::create()
            ->setDbName('dbname.sqlite')
            ->getDumpCommand('dump.sql');

        $expected = "'sqlite3' --bail 'dbname.sqlite' <<<'BEGIN IMMEDIATE;\n.dump' >'dump.sql'";

        $this->assertEquals($expected, $dumpCommand);
    }

    /** @test */
    public function it_can_generate_a_dump_command_with_absolute_paths()
    {
        $dumpCommand = Sqlite::create()
            ->setDbName('/path/to/dbname.sqlite')
            ->setDumpBinaryPath('/usr/bin')
            ->getDumpCommand('/save/to/dump.sql');

        $expected = "'/usr/bin/sqlite3' --bail '/path/to/dbname.sqlite' <<<'BEGIN IMMEDIATE;\n.dump' >'/save/to/dump.sql'";

        $this->assertEquals($expected, $dumpCommand);
    }

    /** @test */
    public function it_successfully_creates_a_backup()
    {
        $dbPath = __DIR__.'/stubs/database.sqlite';
        $dbBackupPath = __DIR__.'/temp/backup.sql';

        Sqlite::create()
            ->setDbName($dbPath)
            ->dumpToFile($dbBackupPath);

        $this->assertFileExists($dbBackupPath);
        $this->assertNotEquals(0, filesize($dbBackupPath), 'Sqlite dump cannot be empty');
    }
}
