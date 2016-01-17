<?php

namespace Spatie\DbDumper\Test;

use PHPUnit_Framework_TestCase;
use Spatie\DbDumper\Databases\MySql;
use Spatie\DbDumper\Exceptions\CannotStartDump;

class MySqlTest extends PHPUnit_Framework_TestCase
{
    /** @test */
    public function it_provides_a_factory_method()
    {
        $this->assertInstanceOf(MySql::class, MySql::create());
    }

    /** @test */
    public function it_will_throw_an_exception_when_no_credentials_are_set()
    {
        $this->setExpectedException(CannotStartDump::class);

        MySql::create()->dumpToFile('test.sql');
    }

    /** @test */
    public function it_can_generate_a_dump_command()
    {
        $dumpCommand = MySql::create()
            ->setDbName('test')
            ->setUserName('test')
            ->setPassword('test')
            ->getDumpCommand('dump.sql', 'credentials.txt');

        $this->assertSame('mysqldump --defaults-extra-file=credentials.txt --skip-comments --extended-insert test > dump.sql', $dumpCommand);
    }

    /** @test */
    public function it_can_generate_a_dump_command_with_custom_binary_path()
    {
        $dumpCommand = MySql::create()
            ->setDbName('test')
            ->setUserName('test')
            ->setPassword('test')
            ->setDumpBinaryPath('/custom/directory')
            ->getDumpCommand('dump.sql', 'credentials.txt');

        $this->assertSame('/custom/directory/mysqldump --defaults-extra-file=credentials.txt --skip-comments --extended-insert test > dump.sql', $dumpCommand);
    }

    /** @test */
    public function it_can_generate_a_dump_command_without_using_extending_inserts()
    {
        $dumpCommand = MySql::create()
            ->setDbName('test')
            ->setUserName('test')
            ->setPassword('test')
            ->doNotUseExtendedInserts()
            ->getDumpCommand('dump.sql', 'credentials.txt');

        $this->assertSame('mysqldump --defaults-extra-file=credentials.txt --skip-comments --skip-extended-insert test > dump.sql', $dumpCommand);
    }

    /** @test */
    public function it_can_generate_a_dump_command_with_a_custom_socket()
    {
        $dumpCommand = MySql::create()
            ->setDbName('test')
            ->setUserName('test')
            ->setPassword('test')
            ->setSocket(1234)
            ->getDumpCommand('dump.sql', 'credentials.txt');

        $this->assertSame('mysqldump --defaults-extra-file=credentials.txt --skip-comments --extended-insert --socket=1234 test > dump.sql', $dumpCommand);
    }

    /** @test */
    public function it_can_dump_a_database()
    {

        if (! $this->runningOnTravis()) {
            return;
        }

        $testFileName = __DIR__ . '/files/dump.sql';

        MySql::create()
            ->setDbName('test')
            ->setUserName('travis')
            ->dumpToFile($testFileName);

        echo file_get_contents($testFileName);
    }

    protected function runningOnTravis()
    {
        return getenv('TRAVIS');

    }
}
