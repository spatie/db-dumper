<?php

namespace Spatie\DbDumper\Databases;

use Spatie\DbDumper\DbDumper;
use Spatie\DbDumper\Exceptions\CannotStartDump;
use Symfony\Component\Process\Process;

class MySql extends DbDumper
{
    protected $socket = 0;
    protected $useExtendedInserts = true;

    /**
     * MySql constructor.
     */
    public function __construct()
    {
        $this->port = 3306;
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
            "--defaults-extra-file={$temporaryCredentialsFile}",
            '--skip-comments',
            $this->useExtendedInserts ? '--extended-insert' : '--skip-extended-insert',
        ];

        if ($this->socket > 0) {
            $command[] = "--socket={$this->socket}";
        }

        $command[] = "{$this->dbName} > {$dumpFile}";

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
            if ($this->$requiredProperty === '') {
                throw CannotStartDump::emptyParameter($requiredProperty);
            }
        }
    }
}
