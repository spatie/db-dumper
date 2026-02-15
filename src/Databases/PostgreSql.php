<?php

namespace Spatie\DbDumper\Databases;

use Spatie\DbDumper\DbDumper;
use Spatie\DbDumper\Exceptions\CannotStartDump;
use Symfony\Component\Process\Process;

class PostgreSql extends DbDumper
{
    protected bool $useInserts = false;

    protected bool $createTables = true;

    protected bool $includeData = true;

    public function __construct()
    {
        $this->port = 5432;
    }

    public function useInserts(): static
    {
        $this->useInserts = true;

        return $this;
    }

    public function dumpToFile(string $dumpFile): void
    {
        $this->guardAgainstIncompleteCredentials();

        $tempFileHandle = tmpfile();
        $this->setTempFileHandle($tempFileHandle);

        $process = $this->getProcess($dumpFile);

        $process->run();

        $this->checkIfDumpWasSuccessFul($process, $dumpFile);
    }

    public function getDumpCommand(string $dumpFile): string
    {
        $quote = $this->determineQuote();

        $command = [
            "{$quote}{$this->dumpBinaryPath}pg_dump{$quote}",
            "-U \"{$this->userName}\"",
            '-h '.($this->socket === '' ? $this->host : $this->socket),
            "-p {$this->port}",
        ];

        if ($this->useInserts) {
            $command[] = '--inserts';
        }

        if (! $this->createTables) {
            $command[] = '--data-only';
        }

        if (! $this->includeData) {
            $command[] = '--schema-only';
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
            $this->escapeCredentialEntry($this->host),
            $this->escapeCredentialEntry((string) $this->port),
            $this->escapeCredentialEntry($this->dbName),
            $this->escapeCredentialEntry($this->userName),
            $this->escapeCredentialEntry($this->password),
        ];

        return implode(':', $contents);
    }

    protected function escapeCredentialEntry(string $entry): string
    {
        return str_replace(['\\', ':'], ['\\\\', '\\:'], $entry);
    }

    public function guardAgainstIncompleteCredentials(): void
    {
        foreach (['userName', 'dbName', 'host'] as $requiredProperty) {
            if ($this->$requiredProperty === '') {
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

    public function doNotCreateTables(): static
    {
        $this->createTables = false;

        return $this;
    }

    public function doNotDumpData(): static
    {
        $this->includeData = false;

        return $this;
    }

    public function getProcess(string $dumpFile): Process
    {
        $command = $this->getDumpCommand($dumpFile);

        fwrite($this->getTempFileHandle(), $this->getContentsOfCredentialsFile());
        $temporaryCredentialsFile = stream_get_meta_data($this->getTempFileHandle())['uri'];

        $envVars = $this->getEnvironmentVariablesForDumpCommand($temporaryCredentialsFile);

        return Process::fromShellCommandline($command, null, $envVars, null, $this->timeout);
    }
}
