<?php

namespace Spatie\DbDumper\Databases;

use Spatie\DbDumper\DbDumper;
use Spatie\DbDumper\Exceptions\CannotStartDump;
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
        if ($dumpBinaryPath !== '' && substr($dumpBinaryPath, -1) !== '/') {
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

    public function dontUseExtendedInserts() : MySql
    {
        $this->useExtendedInserts = false;

        return $this;
    }

    /**
     * Dump the contents of the database to the given file.
     */
    public function dumpToFile(string $dumpFile)
    {
        $this->guardAgainstIncompleteCredentials();

        $tempFileHandle = tmpfile();

        fwrite($tempFileHandle, $this->getContentsOfCredentialsFile());

        $temporaryCredentialsFile = stream_get_meta_data($tempFileHandle)['uri'];

        $command = $this->getDumpCommand($dumpFile, $temporaryCredentialsFile);

        $process = new Process($command);

        $process->run();

        $this->checkIfDumpWasSuccessFul($process, $dumpFile);
    }

    /**
     * Get the command that should be performed to dump the database.
     */
    public function getDumpCommand(string $dumpFile, string $temporaryCredentialsFile) : string
    {
        $command = [
            "{$this->dumpBinaryPath}mysqldump",
            "--defaults-extra-file={$temporaryCredentialsFile}",
            '--skip-comments',
            $this->useExtendedInserts ? '--extended-insert' : '--skip-extended-insert',
        ];

        if ($this->socket != '') {
            $command[] = "--socket={$this->socket}";
        }

        $command[] = "{$this->dbName} > {$dumpFile}";

        return implode(' ', $command);
    }

    public function getContentsOfCredentialsFile() : string
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
