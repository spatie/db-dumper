<?php

namespace Spatie\DbDumper\Test;

use PHPUnit_Framework_TestCase;
use Spatie\DbDumper\Databases\MySql;
use Spatie\DbDumper\Exceptions\CannotStartDump;
use Spatie\DbDumper\Exceptions\CannotSetParameter;

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
        $this->expectException(CannotStartDump::class);

        MySql::create()->dumpToFile('test.sql');
    }

    /** @test */
    public function it_can_generate_a_dump_command()
    {
        $dumpCommand = MySql::create()
            ->setDbName('dbname')
            ->setUserName('username')
            ->setPassword('password')
            ->getDumpCommand('dump.sql', 'credentials.txt');

        $this->assertSame('mysqldump --defaults-extra-file="credentials.txt" --skip-comments --extended-insert dbname > "dump.sql"', $dumpCommand);
    }

    /** @test */
    public function it_can_generate_a_dump_command_without_using_extended_insterts()
    {
        $dumpCommand = MySql::create()
            ->setDbName('dbname')
            ->setUserName('username')
            ->setPassword('password')
            ->dontUseExtendedInserts()
            ->getDumpCommand('dump.sql', 'credentials.txt');

        $this->assertSame('mysqldump --defaults-extra-file="credentials.txt" --skip-comments --skip-extended-insert dbname > "dump.sql"', $dumpCommand);
    }

    /** @test */
    public function it_can_generate_a_dump_command_with_custom_binary_path()
    {
        $dumpCommand = MySql::create()
            ->setDbName('dbname')
            ->setUserName('username')
            ->setPassword('password')
            ->setDumpBinaryPath('/custom/directory')
            ->getDumpCommand('dump.sql', 'credentials.txt');

        $this->assertSame('/custom/directory/mysqldump --defaults-extra-file="credentials.txt" --skip-comments --extended-insert dbname > "dump.sql"', $dumpCommand);
    }

    /** @test */
    public function it_can_generate_a_dump_command_without_using_extending_inserts()
    {
        $dumpCommand = MySql::create()
            ->setDbName('dbname')
            ->setUserName('username')
            ->setPassword('password')
            ->dontUseExtendedInserts()
            ->getDumpCommand('dump.sql', 'credentials.txt');

        $this->assertSame('mysqldump --defaults-extra-file="credentials.txt" --skip-comments --skip-extended-insert dbname > "dump.sql"', $dumpCommand);
    }

    /** @test */
    public function it_can_generate_a_dump_command_using_single_transaction()
    {
        $dumpCommand = MySql::create()
            ->setDbName('dbname')
            ->setUserName('username')
            ->setPassword('password')
            ->useSingleTransaction()
            ->getDumpCommand('dump.sql', 'credentials.txt');

        $this->assertSame('mysqldump --defaults-extra-file="credentials.txt" --skip-comments --extended-insert --single-transaction dbname > "dump.sql"', $dumpCommand);
    }

    /** @test */
    public function it_can_generate_a_dump_command_with_a_custom_socket()
    {
        $dumpCommand = MySql::create()
            ->setDbName('dbname')
            ->setUserName('username')
            ->setPassword('password')
            ->setSocket(1234)
            ->getDumpCommand('dump.sql', 'credentials.txt');

        $this->assertSame('mysqldump --defaults-extra-file="credentials.txt" --skip-comments --extended-insert --socket=1234 dbname > "dump.sql"', $dumpCommand);
    }

    /** @test */
    public function it_can_generate_a_dump_command_for_specific_tables_as_array()
    {
        $dumpCommand = MySql::create()
            ->setDbName('dbname')
            ->setUserName('username')
            ->setPassword('password')
            ->includeTables(['tb1', 'tb2', 'tb3'])
            ->getDumpCommand('dump.sql', 'credentials.txt');

        $this->assertSame('mysqldump --defaults-extra-file="credentials.txt" --skip-comments --extended-insert dbname tb1 tb2 tb3 > "dump.sql"', $dumpCommand);
    }

    /** @test */
    public function it_can_generate_a_dump_command_for_specific_tables_as_string()
    {
        $dumpCommand = MySql::create()
            ->setDbName('dbname')
            ->setUserName('username')
            ->setPassword('password')
            ->includeTables('tb1 tb2 tb3')
            ->getDumpCommand('dump.sql', 'credentials.txt');

        $this->assertSame('mysqldump --defaults-extra-file="credentials.txt" --skip-comments --extended-insert dbname tb1 tb2 tb3 > "dump.sql"', $dumpCommand);
    }

    /** @test */
    public function it_will_throw_an_exception_when_setting_exclude_tables_after_setting_tables()
    {
        $this->setExpectedException(CannotSetParameter::class);

        $dumpCommand = MySql::create()
            ->setDbName('dbname')
            ->setUserName('username')
            ->setPassword('password')
            ->includeTables('tb1 tb2 tb3')
            ->excludeTables('tb4 tb5 tb6');
    }

    /** @test */
    public function it_can_generate_a_dump_command_excluding_tables_as_array()
    {
        $dumpCommand = MySql::create()
            ->setDbName('dbname')
            ->setUserName('username')
            ->setPassword('password')
            ->excludeTables(['tb1', 'tb2', 'tb3'])
            ->getDumpCommand('dump.sql', 'credentials.txt');

        $this->assertSame('mysqldump --defaults-extra-file="credentials.txt" --skip-comments --extended-insert '.
            '--ignore-table=tb1 --ignore-table=tb2 --ignore-table=tb3 dbname > "dump.sql"', $dumpCommand);
    }

    /** @test */
    public function it_can_generate_a_dump_command_excluding_tables_as_string()
    {
        $dumpCommand = MySql::create()
            ->setDbName('dbname')
            ->setUserName('username')
            ->setPassword('password')
            ->excludeTables('tb1, tb2, tb3')
            ->getDumpCommand('dump.sql', 'credentials.txt');

        $this->assertSame('mysqldump --defaults-extra-file="credentials.txt" --skip-comments --extended-insert '.
            '--ignore-table=tb1 --ignore-table=tb2 --ignore-table=tb3 dbname > "dump.sql"', $dumpCommand);
    }

    /** @test */
    public function it_will_throw_an_exception_when_setting_tables_after_setting_esclude_tables()
    {
        $this->expectException(CannotSetParameter::class);

        $dumpCommand = MySql::create()
            ->setDbName('dbname')
            ->setUserName('username')
            ->setPassword('password')
            ->excludeTables('tb1 tb2 tb3')
            ->includeTables('tb4 tb5 tb6');
    }

    /** @test */
    public function it_can_generate_the_contents_of_a_credentials_file()
    {
        $credentialsFileContent = MySql::create()
            ->setDbName('dbname')
            ->setUserName('username')
            ->setPassword('password')
            ->setHost('hostname')
            ->setSocket(1234)
            ->getContentsOfCredentialsFile();

        $this->assertSame(
            '[client]'.PHP_EOL."user = 'username'".PHP_EOL."password = 'password'".PHP_EOL."host = 'hostname'".PHP_EOL."port = '3306'",
            $credentialsFileContent);
    }

    /** @test */
    public function it_can_get_the_name_of_the_db()
    {
        $dbName = 'testName';

        $dbDumper = MySql::create()->setDbName($dbName);

        $this->assertEquals($dbName, $dbDumper->getDbName());
    }
}
