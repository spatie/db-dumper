<?php

namespace Spatie\DbDumper\Databases;

use Spatie\DbDumper\DbDumper;
use Spatie\DbDumper\Exceptions\CannotStartDump;
use Symfony\Component\Process\Process;

class PostgreSql extends DbDumper
{
    /** @var bool */
    protected $useInserts = false;

    /** @var bool */
    protected $createTables = true;

    /** @var false|resource */
    private $tempFileHandle;

    public function __construct()
    {
        $this->port = 5432;
    }

    /**
     * @return $this
     */
    public function useInserts()
    {
        $this->useInserts = true;

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
        $this->setTempFileHandle($tempFileHandle);

        $process = $this->getProcess($dumpFile);

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
        $quote = $this->determineQuote();

        $command = [
            "{$quote}{$this->dumpBinaryPath}pg_dump{$quote}",
            "-U {$this->userName}",
            '-h '.($this->socket === '' ? $this->host : $this->socket),
            "-p {$this->port}",
        ];

        if ($this->useInserts) {
            $command[] = '--inserts';
        }

        if (! $this->createTables) {
            $command[] = '--data-only';
        }

        foreach ($this->extraOptions as $extraOption) {
            $command[] = $extraOption;
        }

        if (! empty($this->includeTables)) {
            $command[] = '-t '.implode(' -t ', $this->includeTables);
        }

        if (! empty($this->excludeTables)) {
            $command[] = '-T '.implode(' -T ', $this->excludeTables);
        }

        return $this->echoToFile(implode(' ', $command), $dumpFile);
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

    public function guardAgainstIncompleteCredentials()
    {
        foreach (['userName', 'dbName', 'host'] as $requiredProperty) {
            if (empty($this->$requiredProperty)) {
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

    /**
     * @return $this
     */
    public function doNotCreateTables()
    {
        $this->createTables = false;

        return $this;
    }

    /**
     * @param string $dumpFile
     * @return Process
     */
    public function getProcess(string $dumpFile): Process
    {
        $command = $this->getDumpCommand($dumpFile);

        fwrite($this->getTempFileHandle(), $this->getContentsOfCredentialsFile());
        $temporaryCredentialsFile = stream_get_meta_data($this->getTempFileHandle())['uri'];

        $envVars = $this->getEnvironmentVariablesForDumpCommand($temporaryCredentialsFile);

        return Process::fromShellCommandline($command, null, $envVars, null, $this->timeout);
    }

    /**
     * @return false|resource
     */
    public function getTempFileHandle()
    {
        return $this->tempFileHandle;
    }

    /**
     * @param false|resource $tempFileHandle
     */
    public function setTempFileHandle($tempFileHandle)
    {
        $this->tempFileHandle = $tempFileHandle;
    }
}
