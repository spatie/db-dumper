<?php

namespace Spatie\DbDumper;

use Spatie\DbDumper\Exceptions\CannotSetParameter;
use Spatie\DbDumper\Exceptions\DumpFailed;
use Symfony\Component\Process\Process;

abstract class DbDumper
{
    /** @var string */
    protected $dbName;

    /** @var string */
    protected $userName;

    /** @var string */
    protected $password;

    /** @var string */
    protected $host = 'localhost';

    /** @var int */
    protected $port = 5432;

    /** @var string */
    protected $socket = '';

    /** @var int */
    protected $timeout = 0;

    /** @var string */
    protected $dumpBinaryPath = '';

    /** @var array */
    protected $includeTables = [];

    /** @var array */
    protected $excludeTables = [];

    /** @var array */
    protected $extraOptions = [];

    public static function create()
    {
        return new static();
    }

    public function getDbName(): string
    {
        return $this->dbName;
    }

    /**
     * @param string $dbName
     *
     * @return \Spatie\DbDumper\Databases\PostgreSql
     */
    public function setDbName(string $dbName)
    {
        $this->dbName = $dbName;

        return $this;
    }

    /**
     * @param string $userName
     *
     * @return \Spatie\DbDumper\Databases\PostgreSql
     */
    public function setUserName(string $userName)
    {
        $this->userName = $userName;

        return $this;
    }

    /**
     * @param string $password
     *
     * @return \Spatie\DbDumper\Databases\PostgreSql
     */
    public function setPassword(string $password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @param string $host
     *
     * @return \Spatie\DbDumper\Databases\PostgreSql
     */
    public function setHost(string $host)
    {
        $this->host = $host;

        return $this;
    }

    /**
     * @param int $port
     *
     * @return \Spatie\DbDumper\Databases\PostgreSql
     */
    public function setPort(int $port)
    {
        $this->port = $port;

        return $this;
    }

    /**
     * @param string $socket
     *
     * @return \Spatie\DbDumper\Databases\PostgreSql
     */
    public function setSocket(string $socket)
    {
        $this->socket = $socket;

        return $this;
    }

    /**
     * @param int $timeout
     *
     * @return \Spatie\DbDumper\Databases\PostgreSql
     */
    public function setTimeout(int $timeout)
    {
        $this->timeout = $timeout;

        return $this;
    }

    /**
     * @param string $dumpBinaryPath
     *
     * @return \Spatie\DbDumper\Databases\PostgreSql
     */
    public function setDumpBinaryPath(string $dumpBinaryPath)
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
     *
     * @throws \Spatie\DbDumper\Exceptions\CannotSetParameter
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
     *
     * @throws \Spatie\DbDumper\Exceptions\CannotSetParameter
     */
    public function excludeTables($excludeTables)
    {
        if (!empty($this->includeTables)) {
            throw CannotSetParameter::conflictingParameters('excludeTables', 'includeTables');
        }

        if (!is_array($excludeTables)) {
            $excludeTables = explode(', ', $excludeTables);
        }

        $this->excludeTables = $excludeTables;

        return $this;
    }

    public function addExtraOption(string $extraOption = '')
    {
        if (! empty($extraOption)) {
            $this->extraOptions[] = $extraOption;
        }

        return $this;
    }

    abstract public function dumpToFile(string $dumpFile);

    protected function checkIfDumpWasSuccessFul(Process $process, string $outputFile): bool
    {
        if (!$process->isSuccessful()) {
            throw DumpFailed::processDidNotEndSuccessfully($process);
        }

        if (!file_exists($outputFile)) {
            throw DumpFailed::dumpfileWasNotCreated();
        }

        if (filesize($outputFile) === 0) {
            throw DumpFailed::dumpfileWasEmpty();
        }

        return true;
    }
}
