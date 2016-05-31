<?php

namespace Spatie\DbDumper\Databases;

use Spatie\DbDumper\DbDumper;
use Spatie\DbDumper\Exceptions\CannotStartDump;
use Spatie\DbDumper\Exceptions\CannotSetParameter;
use Symfony\Component\Process\Process;

class MySql extends DbDumper
{
    protected $dbName;
    protected $userName;
    protected $password;
    protected $host = 'localhost';
    protected $port = 3306;
    protected $socket;
    protected $dumpBinaryPath = '';
    protected $useExtendedInserts = true;
    protected $useSingleTransaction = false;
    protected $tables = array();
    protected $excludeTables = array();
    protected $timeout;

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
     * @param int $socket
     *
     * @return \Spatie\DbDumper\Databases\MySql
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
     * @return \Spatie\DbDumper\Databases\MySql
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
     * @return \Spatie\DbDumper\Databases\MySql
     */
    public function useExtendedInserts()
    {
        $this->useExtendedInserts = true;

        return $this;
    }

    /**
     * @return \Spatie\DbDumper\Databases\MySql
     */
    public function dontUseExtendedInserts()
    {
        $this->useExtendedInserts = false;

        return $this;
    }

    /**
     * @return \Spatie\DbDumper\Databases\MySql
     */
    public function useSingleTransaction()
    {
        $this->useSingleTransaction = true;

        return $this;
    }

    /**
     * @return \Spatie\DbDumper\Databases\MySql
     */
    public function dontUseSingleTransaction()
    {
        $this->useSingleTransaction = false;

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

        $tempFileHandle = tmpfile();
        fwrite($tempFileHandle, $this->getContentsOfCredentialsFile());
        $temporaryCredentialsFile = stream_get_meta_data($tempFileHandle)['uri'];

        $command = $this->getDumpCommand($dumpFile, $temporaryCredentialsFile);

        $process = new Process($command);

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
     * @param string $temporaryCredentialsFile
     *
     * @return string
     */
    public function getDumpCommand($dumpFile, $temporaryCredentialsFile)
    {
        $command = [
            "{$this->dumpBinaryPath}mysqldump",
            "--defaults-extra-file=\"{$temporaryCredentialsFile}\"",
            '--skip-comments',
            $this->useExtendedInserts ? '--extended-insert' : '--skip-extended-insert',
        ];

        if ($this->useSingleTransaction) {
            $command[] = "--single-transaction";
        }

        if ($this->socket != '') {
            $command[] = "--socket={$this->socket}";
        }

        if (!empty($this->excludeTables)) {
            $command[] = '--ignore-table=' . implode(' --ignore-table=', $this->excludeTables);
        }

        $command[] = "{$this->dbName}";

        if (!empty($this->tables)) {
            $command[] = implode(' ', $this->tables);
        }

        $command[] = "> \"{$dumpFile}\"";

        return implode(' ', $command);
    }

    /**
     * @return string
     */
    public function getContentsOfCredentialsFile()
    {
        $contents = [
            '[client]',
            "user = '{$this->userName}'",
            "password = '{$this->password}'",
            "host = '{$this->host}'",
            "port = '{$this->port}'",
        ];

        return implode(PHP_EOL, $contents);
    }

    protected function guardAgainstIncompleteCredentials()
    {
        foreach (['userName', 'dbName', 'host'] as $requiredProperty) {
            if (strlen($this->$requiredProperty) === 0) {
                throw CannotStartDump::emptyParameter($requiredProperty);
            }
        }
    }
}
