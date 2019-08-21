<?php

namespace Spatie\DbDumper\Test;

use PHPUnit\Framework\TestCase;
use Spatie\DbDumper\Databases\PostgreSql;
use Spatie\DbDumper\Compressors\GzipCompressor;
use Spatie\DbDumper\Exceptions\CannotStartDump;
use Spatie\DbDumper\Exceptions\CannotSetParameter;

class PostgreSqlTest extends TestCase
{
    /** @test */
    public function it_provides_a_factory_method()
    {
        $this->assertInstanceOf(PostgreSql::class, PostgreSql::create());
    }

    /** @test */
    public function it_will_throw_an_exception_when_no_credentials_are_set()
    {
        $this->expectException(CannotStartDump::class);

        PostgreSql::create()->dumpToFile('test.sql');
    }

    /** @test */
    public function it_can_generate_a_dump_command()
    {
        $dumpCommand = PostgreSql::create()
            ->setDbName('dbname')
            ->setUserName('username')
            ->setPassword('password')
            ->getDumpCommand('dump.sql');

        $this->assertSame('\'pg_dump\' -U username -h localhost -p 5432 > "dump.sql"', $dumpCommand);
    }

    /** @test */
    public function it_can_generate_a_dump_command_with_compression_enabled()
    {
        $dumpCommand = PostgreSql::create()
            ->setDbName('dbname')
            ->setUserName('username')
            ->setPassword('password')
            ->enableCompression()
            ->getDumpCommand('dump.sql');

        $expected = '((((\'pg_dump\' -U username -h localhost -p 5432; echo $? >&3) | gzip > "dump.sql") 3>&1) | (read x; exit $x))';

        $this->assertSame($expected, $dumpCommand);
    }

    /** @test */
    public function it_can_generate_a_dump_command_with_gzip_compressor_enabled()
    {
        $dumpCommand = PostgreSql::create()
            ->setDbName('dbname')
            ->setUserName('username')
            ->setPassword('password')
            ->useCompressor(new GzipCompressor)
            ->getDumpCommand('dump.sql');

        $expected = '((((\'pg_dump\' -U username -h localhost -p 5432; echo $? >&3) | gzip > "dump.sql") 3>&1) | (read x; exit $x))';

        $this->assertSame($expected, $dumpCommand);
    }

    /** @test */
    public function it_can_generate_a_dump_command_with_absolute_path_having_space_and_brackets()
    {
        $dumpCommand = PostgreSql::create()
            ->setDbName('dbname')
            ->setUserName('username')
            ->setPassword('password')
            ->getDumpCommand('/save/to/new (directory)/dump.sql');

        $this->assertSame('\'pg_dump\' -U username -h localhost -p 5432 > "/save/to/new (directory)/dump.sql"', $dumpCommand);
    }

    /** @test */
    public function it_can_generate_a_dump_command_with_using_inserts()
    {
        $dumpCommand = PostgreSql::create()
            ->setDbName('dbname')
            ->setUserName('username')
            ->setPassword('password')
            ->useInserts()
            ->getDumpCommand('dump.sql');

        $this->assertSame('\'pg_dump\' -U username -h localhost -p 5432 --inserts > "dump.sql"', $dumpCommand);
    }

    /** @test */
    public function it_can_generate_a_dump_command_with_a_custom_port()
    {
        $dumpCommand = PostgreSql::create()
            ->setDbName('dbname')
            ->setUserName('username')
            ->setPassword('password')
            ->setPort(1234)
            ->getDumpCommand('dump.sql');

        $this->assertSame('\'pg_dump\' -U username -h localhost -p 1234 > "dump.sql"', $dumpCommand);
    }

    /** @test */
    public function it_can_generate_a_dump_command_with_custom_binary_path()
    {
        $dumpCommand = PostgreSql::create()
            ->setDbName('dbname')
            ->setUserName('username')
            ->setPassword('password')
            ->setDumpBinaryPath('/custom/directory')
            ->getDumpCommand('dump.sql');

        $this->assertSame('\'/custom/directory/pg_dump\' -U username -h localhost -p 5432 > "dump.sql"', $dumpCommand);
    }

    /** @test */
    public function it_can_generate_a_dump_command_with_a_custom_socket()
    {
        $dumpCommand = PostgreSql::create()
            ->setDbName('dbname')
            ->setUserName('username')
            ->setPassword('password')
            ->setSocket('/var/socket.1234')
            ->getDumpCommand('dump.sql');

        $this->assertEquals('\'pg_dump\' -U username -h /var/socket.1234 -p 5432 > "dump.sql"', $dumpCommand);
    }

    /** @test */
    public function it_can_generate_a_dump_command_for_specific_tables_as_array()
    {
        $dumpCommand = PostgreSql::create()
            ->setDbName('dbname')
            ->setUserName('username')
            ->setPassword('password')
            ->includeTables(['tb1', 'tb2', 'tb3'])
            ->getDumpCommand('dump.sql', 'credentials.txt');

        $this->assertSame('\'pg_dump\' -U username -h localhost -p 5432 -t tb1 -t tb2 -t tb3 > "dump.sql"', $dumpCommand);
    }

    /** @test */
    public function it_can_generate_a_dump_command_for_specific_tables_as_string()
    {
        $dumpCommand = PostgreSql::create()
            ->setDbName('dbname')
            ->setUserName('username')
            ->setPassword('password')
            ->includeTables('tb1, tb2, tb3')
            ->getDumpCommand('dump.sql', 'credentials.txt');

        $this->assertSame('\'pg_dump\' -U username -h localhost -p 5432 -t tb1 -t tb2 -t tb3 > "dump.sql"', $dumpCommand);
    }

    /** @test */
    public function it_will_throw_an_exception_when_setting_exclude_tables_after_setting_tables()
    {
        $this->expectException(CannotSetParameter::class);

        PostgreSql::create()
            ->setDbName('dbname')
            ->setUserName('username')
            ->setPassword('password')
            ->includeTables('tb1, tb2, tb3')
            ->excludeTables('tb4, tb5, tb6');
    }

    /** @test */
    public function it_can_generate_a_dump_command_excluding_tables_as_array()
    {
        $dumpCommand = PostgreSql::create()
            ->setDbName('dbname')
            ->setUserName('username')
            ->setPassword('password')
            ->excludeTables(['tb1', 'tb2', 'tb3'])
            ->getDumpCommand('dump.sql', 'credentials.txt');

        $this->assertSame('\'pg_dump\' -U username -h localhost -p 5432 -T tb1 -T tb2 -T tb3 > "dump.sql"', $dumpCommand);
    }

    /** @test */
    public function it_can_generate_a_dump_command_excluding_tables_as_string()
    {
        $dumpCommand = PostgreSql::create()
            ->setDbName('dbname')
            ->setUserName('username')
            ->setPassword('password')
            ->excludeTables('tb1, tb2, tb3')
            ->getDumpCommand('dump.sql', 'credentials.txt');

        $this->assertSame('\'pg_dump\' -U username -h localhost -p 5432 -T tb1 -T tb2 -T tb3 > "dump.sql"', $dumpCommand);
    }

    /** @test */
    public function it_will_throw_an_exception_when_setting_tables_after_setting_exclude_tables()
    {
        $this->expectException(CannotSetParameter::class);

        PostgreSql::create()
            ->setDbName('dbname')
            ->setUserName('username')
            ->setPassword('password')
            ->excludeTables('tb1, tb2, tb3')
            ->includeTables('tb4, tb5, tb6');
    }

    /** @test */
    public function it_can_generate_the_contents_of_a_credentials_file()
    {
        $credentialsFileContent = PostgreSql::create()
            ->setDbName('dbname')
            ->setUserName('username')
            ->setPassword('password')
            ->setHost('hostname')
            ->setPort(5432)
            ->getContentsOfCredentialsFile();

        $this->assertSame('hostname:5432:dbname:username:password', $credentialsFileContent);
    }

    /** @test */
    public function it_can_get_the_name_of_the_db()
    {
        $dbName = 'testName';

        $dbDumper = PostgreSql::create()->setDbName($dbName);

        $this->assertEquals($dbName, $dbDumper->getDbName());
    }

    /** @test */
    public function it_can_add_an_extra_option()
    {
        $dumpCommand = PostgreSql::create()
            ->setDbName('dbname')
            ->setUserName('username')
            ->setPassword('password')
            ->addExtraOption('-something-else')
            ->getDumpCommand('dump.sql');

        $this->assertSame('\'pg_dump\' -U username -h localhost -p 5432 -something-else > "dump.sql"', $dumpCommand);
    }

    /** @test */
    public function it_can_get_the_host()
    {
        $dumper = PostgreSql::create()->setHost('myHost');

        $this->assertEquals('myHost', $dumper->getHost());
    }
}
