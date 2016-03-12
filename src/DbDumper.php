<?php

namespace Spatie\DbDumper;

use Spatie\DbDumper\Exceptions\DumpFailed;
use Symfony\Component\Process\Process;

abstract class DbDumper
{
    protected $dbName;
    protected $userName;
    protected $password;
    protected $host = 'localhost';
    protected $port = 0;
    protected $dumpBinaryPath = '';

    public static function create()
    {
        return new static();
    }

    /**
     * Dump the contents of the database to the given file.
     *
     * @param string $dumpFile
     */
    abstract public function dumpToFile($dumpFile);

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
     * @return \Spatie\DbDumper\Databases\MySql
     */
    public function setDbName($dbName)
    {
        $this->dbName = $dbName;

        return $this;
    }

    /**
     * @param string $userName
     *
     * @return \Spatie\DbDumper\Databases\MySql
     */
    public function setUserName($userName)
    {
        $this->userName = $userName;

        return $this;
    }

    /**
     * @param string $password
     *
     * @return \Spatie\DbDumper\Databases\MySql
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @param string $host
     *
     * @return \Spatie\DbDumper\Databases\MySql
     */
    public function setHost($host)
    {
        $this->host = $host;

        return $this;
    }

    /**
     * @param int $port
     *
     * @return \Spatie\DbDumper\Databases\MySql
     */
    public function setPort($port)
    {
        $this->port = $port;

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
     * @param \Symfony\Component\Process\Process $process
     * @param string                             $outputFile
     *
     * @return bool
     *
     * @throws \Spatie\DbDumper\Exceptions\DumpFailed
     */
    protected function checkIfDumpWasSuccessFul(Process $process, $outputFile)
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
