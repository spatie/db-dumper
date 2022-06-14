<?php

namespace Spatie\DbDumper\Databases;

use Spatie\DbDumper\DbDumper;
use Spatie\DbDumper\Exceptions\CannotStartDump;
use Symfony\Component\Process\Process;

class MongoDb extends DbDumper
{
    protected int $port = 27017;

    protected ?string $collection = null;

    protected ?string $authenticationDatabase = null;

    public function dumpToFile(string $dumpFile): void
    {
        $this->guardAgainstIncompleteCredentials();

        $process = $this->getProcess($dumpFile);

        $process->run();

        $this->checkIfDumpWasSuccessFul($process, $dumpFile);
    }

    /**
     * Verifies if the dbname and host options are set.
     *
     * @throws \Spatie\DbDumper\Exceptions\CannotStartDump
     *
     * @return void
     */
    public function guardAgainstIncompleteCredentials(): void
    {
        foreach (['dbName', 'host'] as $requiredProperty) {
            if (strlen($this->$requiredProperty) === 0) {
                throw CannotStartDump::emptyParameter($requiredProperty);
            }
        }
    }

    public function setCollection(string $collection): self
    {
        $this->collection = $collection;

        return $this;
    }

    public function setAuthenticationDatabase(string $authenticationDatabase): self
    {
        $this->authenticationDatabase = $authenticationDatabase;

        return $this;
    }

    public function getDumpCommand(string $filename): string
    {
        $quote = $this->determineQuote();

        $command = [
            "{$quote}{$this->dumpBinaryPath}mongodump{$quote}",
            "--db {$this->dbName}",
            '--archive',
        ];

        if ($this->userName) {
            $command[] = "--username {$quote}{$this->userName}{$quote}";
        }

        if ($this->password) {
            $command[] = "--password {$quote}{$this->password}{$quote}";
        }

        if (isset($this->host)) {
            $command[] = "--host {$this->host}";
        }

        if (isset($this->port)) {
            $command[] = "--port {$this->port}";
        }

        if (isset($this->collection)) {
            $command[] = "--collection {$this->collection}";
        }

        if ($this->authenticationDatabase) {
            $command[] = "--authenticationDatabase {$this->authenticationDatabase}";
        }

        return $this->echoToFile(implode(' ', $command), $filename);
    }

    public function getProcess(string $dumpFile): Process
    {
        $command = $this->getDumpCommand($dumpFile);

        return Process::fromShellCommandline($command, null, null, null, $this->timeout);
    }
}
