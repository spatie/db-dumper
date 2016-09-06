<?php

namespace Spatie\DbDumper\Databases;

use Spatie\DbDumper\DbDumper;
use Spatie\DbDumper\Exceptions\CannotStartDump;
use Spatie\DbDumper\Exceptions\CannotSetParameter;
use Symfony\Component\Process\Process;

class MySql extends DbDumper
{
    protected $useExtendedInserts = true;
    protected $useSingleTransaction = false;
    protected $includeTables = [];
    protected $excludeTables = [];
    protected $timeout;

    public function __construct()
    {
        $this->port = 3306;
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
            throw CannotSetParameter::conflictingParameters('excludeTables', 'tables');
        }

        if (!is_array($excludeTables)) {
            $excludeTables = explode(', ', $excludeTables);
        }

        $this->excludeTables = $excludeTables;

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
    public function dumpToFile(string $dumpFile)
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
    public function getDumpCommand(string $dumpFile, string $temporaryCredentialsFile): string
    {
        $command = [
            "{$this->dumpBinaryPath}mysqldump",
            "--defaults-extra-file=\"{$temporaryCredentialsFile}\"",
            '--skip-comments',
            $this->useExtendedInserts ? '--extended-insert' : '--skip-extended-insert',
        ];

        if ($this->useSingleTransaction) {
            $command[] = '--single-transaction';
        }

        if ($this->socket !== 0) {
            $command[] = "--socket={$this->socket}";
        }

        if (!empty($this->excludeTables)) {
            $command[] = '--ignore-table=' . implode(' --ignore-table=', $this->excludeTables);
        }

        $command[] = "{$this->dbName}";

        if (!empty($this->includeTables)) {
            $command[] = implode(' ', $this->includeTables);
        }

        $command[] = "> \"{$dumpFile}\"";

        return implode(' ', $command);
    }

    public function getContentsOfCredentialsFile(): string
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
