<?php

namespace Spatie\DbDumper\Test;

use PHPUnit\Framework\TestCase;
use Spatie\DbDumper\Compressors\Bzip2Compressor;
use Spatie\DbDumper\Compressors\GzipCompressor;
use Spatie\DbDumper\Databases\MongoDb;
use Spatie\DbDumper\Exceptions\CannotStartDump;

class MongoDbTest extends TestCase
{
    /** @test */
    public function it_provides_a_factory_method()
    {
        $this->assertInstanceOf(MongoDb::class, MongoDb::create());
    }

    /** @test */
    public function it_will_throw_an_exception_when_no_credentials_are_set()
    {
        $this->expectException(CannotStartDump::class);

        MongoDb::create()->dumpToFile('test.gz');
    }

    /** @test */
    public function it_can_generate_a_dump_command()
    {
        $dumpCommand = MongoDb::create()
            ->setDbName('dbname')
            ->getDumpCommand('dbname.gz');

        $this->assertSame('\'mongodump\' --db dbname'
            .' --archive --host localhost --port 27017 > "dbname.gz"', $dumpCommand);
    }

    /** @test */
    public function it_can_generate_a_dump_command_with_gzip_compressor_enabled()
    {
        $dumpCommand = MongoDb::create()
            ->setDbName('dbname')
            ->useCompressor(new GzipCompressor)
            ->getDumpCommand('dbname.gz');

        $expected = '((((\'mongodump\' --db dbname --archive --host localhost --port 27017; echo $? >&3) | gzip > "dbname.gz") 3>&1) | (read x; exit $x))';

        $this->assertSame($expected, $dumpCommand);
    }

    /** @test */
    public function it_can_generate_a_dump_command_with_bzip2_compressor_enabled()
    {
        $dumpCommand = MongoDb::create()
            ->setDbName('dbname')
            ->useCompressor(new Bzip2Compressor)
            ->getDumpCommand('dbname.bz2');

        $expected = '((((\'mongodump\' --db dbname --archive --host localhost --port 27017; echo $? >&3) | bzip2 > "dbname.bz2") 3>&1) | (read x; exit $x))';

        $this->assertSame($expected, $dumpCommand);
    }

    /** @test */
    public function it_can_generate_a_dump_command_with_absolute_path_having_space_and_brackets()
    {
        $dumpCommand = MongoDb::create()
            ->setDbName('dbname')
            ->getDumpCommand('/save/to/new (directory)/dbname.gz');

        $this->assertSame('\'mongodump\' --db dbname --archive --host localhost --port 27017 > "/save/to/new (directory)/dbname.gz"', $dumpCommand);
    }

    /** @test */
    public function it_can_generate_a_dump_command_with_username_and_password()
    {
        $dumpCommand = MongoDb::create()
            ->setDbName('dbname')
            ->setUserName('username')
            ->setPassword('password')
            ->getDumpCommand('dbname.gz');

        $this->assertSame('\'mongodump\' --db dbname --archive'
            .' --username \'username\' --password \'password\' --host localhost --port 27017 > "dbname.gz"', $dumpCommand);
    }

    /** @test */
    public function it_can_generate_a_command_with_custom_host_and_port()
    {
        $dumpCommand = MongoDb::create()
            ->setDbName('dbname')
            ->setHost('mongodb.test.com')
            ->setPort(27018)
            ->getDumpCommand('dbname.gz');

        $this->assertSame('\'mongodump\' --db dbname --archive'
         .' --host mongodb.test.com --port 27018 > "dbname.gz"', $dumpCommand);
    }

    /** @test */
    public function it_can_generate_a_backup_command_for_a_single_collection()
    {
        $dumpCommand = MongoDb::create()
            ->setDbName('dbname')
            ->setCollection('mycollection')
            ->getDumpCommand('dbname.gz');

        $this->assertSame('\'mongodump\' --db dbname --archive'
            .' --host localhost --port 27017 --collection mycollection > "dbname.gz"', $dumpCommand);
    }

    /** @test */
    public function it_can_generate_a_dump_command_with_custom_binary_path()
    {
        $dumpCommand = MongoDb::create()
            ->setDbName('dbname')
            ->setDumpBinaryPath('/custom/directory')
            ->getDumpCommand('dbname.gz');

        $this->assertSame('\'/custom/directory/mongodump\' --db dbname --archive'
            .' --host localhost --port 27017 > "dbname.gz"', $dumpCommand);
    }

    /** @test */
    public function it_can_generate_a_dump_command_with_authentication_database()
    {
        $dumpCommand = MongoDb::create()
            ->setDbName('dbname')
            ->setAuthenticationDatabase('admin')
            ->getDumpCommand('dbname.gz');

        $this->assertSame('\'mongodump\' --db dbname --archive'
            .' --host localhost --port 27017 --authenticationDatabase admin > "dbname.gz"', $dumpCommand);
    }
}
