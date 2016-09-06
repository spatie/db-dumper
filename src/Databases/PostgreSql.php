<?php

namespace Spatie\DbDumper\Databases;

use Spatie\DbDumper\DbDumper;
use Spatie\DbDumper\Exceptions\CannotStartDump;
use Spatie\DbDumper\Exceptions\CannotSetParameter;
use Symfony\Component\Process\Process;

class PostgreSql extends DbDumper
{
    protected $useInserts = false;
    protected $includeTables = [];
    protected $excludeTables = [];
    protected $timeout = null;

    public function __construct()
    {
        $this->port = 5432;
    }
    
    /**
     * @param string|array $includeTables
     *
     * @return \Spatie\DbDumper\Databases\PostgreSql
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
     * @return \Spatie\DbDumper\Databases\PostgreSql
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
    public function dumpToFile(string $dumpFile)
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
    public function getDumpCommand(string $dumpFile): string
    {
        $command = [
            "{$this->dumpBinaryPath}pg_dump",
            "-U {$this->userName}",
            '-h '.($this->socket === 0 ? $this->host : $this->socket),
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

    public function getContentsOfCredentialsFile(): string
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

    protected function getEnvironmentVariablesForDumpCommand(string $temporaryCredentialsFile): array
    {
        return [
            'PGPASSFILE' => $temporaryCredentialsFile,
            'PGDATABASE' => $this->dbName,
        ];
    }
}
