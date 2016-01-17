<?php

namespace Spatie\DbDumper\Databases;

use Spatie\DbDumper\DbDumper;
use Spatie\DbDumper\Exceptions\CannotStartDump;

class MySql extends DbDumper
{
    protected $dbName;
    protected $userName;
    protected $password;
    protected $host = 'localhost';
    protected $port;
    protected $socket;
    protected $dumpBinaryPath = '';
    protected $useExtendedInserts = true;

    public function setDbName(string $dbName) : MySql
    {
        $this->dbName = $dbName;

        return $this;
    }

    public function setUserName(string $userName) : MySql
    {
        $this->userName = $userName;

        return $this;
    }

    public function setPassword(string $password) : MySql
    {
        $this->password = $password;

        return $this;
    }

    public function setHost(string $host) : MySql
    {
        $this->host = $host;

        return $this;
    }

    public function setPort(int $port) : MySql
    {
        $this->port = $port;

        return $this;
    }

    public function setSocket(int $socket) : MySql
    {
        $this->socket = $socket;

        return $this;
    }

    public function setDumpBinaryPath(string $dumpBinaryPath) : MySql
    {
        if ($dumpBinaryPath != '' && substr($dumpBinaryPath, -1) != '/') {
            $dumpBinaryPath .= '/';
        }

        $this->dumpBinaryPath = $dumpBinaryPath;

        return $this;
    }

    public function useExtendedInserts() : MySql
    {
        $this->useExtendedInserts = true;

        return $this;
    }

    public function doNotUseExtendedInserts() : MySql
    {
        $this->useExtendedInserts = false;

        return $this;
    }

    /**
     * Dump the contents of the database to the given file.
     */
    public function dumpToFile(string $dumpFile)
    {
        $this->guardAgainstIncompletedCredentials();

        $temporaryCredentialsFile = $this->createTemporaryCredentialsFile();

        $command = $this->getDumpCommand($dumpFile, $temporaryCredentialsFile);

        $this->process
            ->setCommandLine($command)
            ->run();

        unlink($temporaryCredentialsFile);

        $this->checkIfDumpWasSuccessFull($dumpFile);
    }

    /**
     * Get the command that should be performed to dump the database.
     */
    public function getDumpCommand(string $dumpFile, string $temporaryCredentialsFile) : string
    {
        $commandLines = [
            "{$this->dumpBinaryPath}mysqldump",
            "--defaults-extra-file={$temporaryCredentialsFile}",
            '--skip-comments',
            $this->useExtendedInserts ? '--extended-insert' : '--skip-extended-insert',
        ];

        if ($this->socket != '') {
            $commandLines[] = "--socket={$this->socket}";
        }

        $commandLines[] = "{$this->dbName} > {$dumpFile}";

        return implode(' ', $commandLines);
    }

    protected function guardAgainstIncompletedCredentials()
    {
        foreach (['userName', 'dbName', 'host'] as $requiredProperty) {
            if ($this->$requiredProperty == '') {
                throw CannotStartDump::emptyParameter($requiredProperty);
            }
        }
    }

    protected function createTemporaryCredentialsFile() : string
    {
        $contents = [
            '[client]',
            "user = '{$this->userName}'",
            "password = '{$this->password}'",
            "host = '{$this->host}'",
            "port = '{$this->port}'",
        ];

        $tempFileHandle = tmpfile();

        fwrite($tempFileHandle, implode(PHP_EOL, $contents));

        return stream_get_meta_data($tempFileHandle)['uri'];
    }
}
