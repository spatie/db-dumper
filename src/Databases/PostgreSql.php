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
    protected $includeTables = array();
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
     * @param string|array $includeTables
     *
     * @return \Spatie\DbDumper\Databases\MySql
     */
    public function includeTables($includeTables)
    {
        if (!empty($this->excludeTables)) {
            throw CannotSetParameter::conflictingParameters('includeTables', 'excludeTables');
        }

        if (!is_array($includeTables)) {
            $includeTables = explode(', ', $includeTables);
        }

        $this->includeTables = $includeTables;

        return $this;
    }

    /**
     * @param string|array $excludeTables
     *
     * @return \Spatie\DbDumper\Databases\MySql
     */
    public function excludeTables($excludeTables)
    {
        if (!empty($this->includeTables)) {
            throw CannotSetParameter::conflictingParameters('excludeTables', 'tables');
        }

        if (!is_array($excludeTables)) {
            $excludeTables = explode(', ', $excludeTables);
        }

        $this->excludeTables = $excludeTables;

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
            "-U {$this->userName}",
            '-h '.($this->socket === '' ? $this->host : $this->socket),
            "-p {$this->port}",
            "--file=\"{$dumpFile}\"",
        ];

        if ($this->useInserts) {
            $command[] = '--inserts';
        }

        if (!empty($this->includeTables)) {
            $command[] = '-t '.implode(' -t ', $this->includeTables);
        }

        if (!empty($this->excludeTables)) {
            $command[] = '-T '.implode(' -T ', $this->excludeTables);
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
            'PGDATABASE' => $this->dbName,
        ];
    }
}
