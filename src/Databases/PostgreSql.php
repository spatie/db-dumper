<?php

namespace Spatie\DbDumper\Databases;

use Spatie\DbDumper\DbDumper;
use Spatie\DbDumper\Exceptions\CannotStartDump;
use Spatie\DbDumper\Exceptions\CannotSetParameter;
use Symfony\Component\Process\Process;

class PostgreSql extends DbDumper
{
    protected $dbName;
    protected $userName;
    protected $password;
    protected $host = 'localhost';
    protected $port = 5432;
    protected $socket = '';
    protected $dumpBinaryPath = '';
    protected $useInserts = false;
    protected $tables = array();
    protected $excludeTables = array();
    protected $timeout = null;

    /**
     * @return string
     */
    public function getDbName()
    {
        return $this->dbName;
    }

    /**
     * @param string $dbName
     *
     * @return \Spatie\DbDumper\Databases\PostgreSql
     */
    public function setDbName($dbName)
    {
        $this->dbName = $dbName;

        return $this;
    }

    /**
     * @param string $userName
     *
     * @return \Spatie\DbDumper\Databases\PostgreSql
     */
    public function setUserName($userName)
    {
        $this->userName = $userName;

        return $this;
    }

    /**
     * @param string $password
     *
     * @return \Spatie\DbDumper\Databases\PostgreSql
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @param string $host
     *
     * @return \Spatie\DbDumper\Databases\PostgreSql
     */
    public function setHost($host)
    {
        $this->host = $host;

        return $this;
    }

    /**
     * @param int $port
     *
     * @return \Spatie\DbDumper\Databases\PostgreSql
     */
    public function setPort($port)
    {
        $this->port = $port;

        return $this;
    }

    /**
     * @param string $socket
     *
     * @return \Spatie\DbDumper\Databases\PostgreSql
     */
    public function setSocket($socket)
    {
        $this->socket = $socket;

        return $this;
    }

    /**
     * @param int $timeout
     *
     * @return \Spatie\DbDumper\Databases\PostgreSql
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;

        return $this;
    }

    /**
     * @param string $dumpBinaryPath
     *
     * @return \Spatie\DbDumper\Databases\PostgreSql
     */
    public function setDumpBinaryPath($dumpBinaryPath)
    {
        if ($dumpBinaryPath !== '' && substr($dumpBinaryPath, -1) !== '/') {
            $dumpBinaryPath .= '/';
        }

        $this->dumpBinaryPath = $dumpBinaryPath;

        return $this;
    }

    /**
     * @param string/array $tables
     *
     * @return \Spatie\DbDumper\Databases\MySql
     */
    public function setTables($tables)
    {
        if (!empty($this->excludeTables)) {
            throw CannotSetParameter::conflictParameters('tables', 'excludeTables');
        }

        if (is_array($tables)) {
            $this->tables = $tables;

            return $this;
        }

        $this->tables = explode(' ', $tables);

        return $this;
    }

    /**
     * @param string/array $tables
     *
     * @return \Spatie\DbDumper\Databases\MySql
     */
    public function setExcludeTables($tables)
    {
         if (!empty($this->tables)) {
            throw CannotSetParameter::conflictParameters('excludeTables', 'tables');
        }

        if (is_array($tables)) {
            $this->excludeTables = $tables;

            return $this;
        }

        $this->excludeTables = explode(' ', $tables);

        return $this;
    }
    /**
     * @return \Spatie\DbDumper\Databases\PostgreSql
     */
    public function useInserts()
    {
        $this->useInserts = true;

        return $this;
    }

    /**
     * @return \Spatie\DbDumper\Databases\PostgreSql
     */
    public function dontUseInserts()
    {
        $this->useInserts = false;

        return $this;
    }

    /**
     * Dump the contents of the database to the given file.
     *
     * @param string $dumpFile
     *
     * @throws \Spatie\DbDumper\Exceptions\CannotStartDump
     * @throws \Spatie\DbDumper\Exceptions\DumpFailed
     */
    public function dumpToFile($dumpFile)
    {
        $this->guardAgainstIncompleteCredentials();

        $command = $this->getDumpCommand($dumpFile);

        $tempFileHandle = tmpfile();
        fwrite($tempFileHandle, $this->getContentsOfCredentialsFile());
        $temporaryCredentialsFile = stream_get_meta_data($tempFileHandle)['uri'];

        $process = new Process($command, null, $this->getEnvironmentVariablesForDumpCommand($temporaryCredentialsFile));

        if (!is_null($this->timeout)) {
            $process->setTimeout($this->timeout);
        }

        $process->run();

        $this->checkIfDumpWasSuccessFul($process, $dumpFile);
    }

    /**
     * Get the command that should be performed to dump the database.
     *
     * @param string $dumpFile
     *
     * @return string
     */
    public function getDumpCommand($dumpFile)
    {
        $command = [
            "{$this->dumpBinaryPath}pg_dump",
            "-d {$this->dbName}",
            "-U {$this->userName}",
            '-h '.($this->socket === '' ? $this->host : $this->socket),
            "-p {$this->port}",
            "--file=\"{$dumpFile}\"",
        ];

        if ($this->useInserts) {
            $command[] = '--inserts';
        }

        
        if (!empty($this->tables)) {
            $command[] = '-t ' . implode(' -t ', $this->tables);
        }

        if (!empty($this->excludeTables)) {
            $command[] = '-T ' . implode(' -T ', $this->excludeTables);
        }

        return implode(' ', $command);
    }

    /**
     * @return string
     */
    public function getContentsOfCredentialsFile()
    {
        $contents = [
            $this->host,
            $this->port,
            $this->dbName,
            $this->userName,
            $this->password,
        ];

        return implode(':', $contents);
    }

    protected function guardAgainstIncompleteCredentials()
    {
        foreach (['userName', 'dbName', 'host'] as $requiredProperty) {
            if ($this->$requiredProperty == '') {
                throw CannotStartDump::emptyParameter($requiredProperty);
            }
        }
    }

    /**
     * @param $temporaryCredentialsFile
     *
     * @return array
     */
    private function getEnvironmentVariablesForDumpCommand($temporaryCredentialsFile)
    {
        return [
            'PGPASSFILE' => $temporaryCredentialsFile,
        ];
    }
}
