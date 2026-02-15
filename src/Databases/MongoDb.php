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

        $this->checkIfDumpWasSuccessful($process, $dumpFile);
    }

    public function guardAgainstIncompleteCredentials(): void
    {
        foreach (['dbName', 'host'] as $requiredProperty) {
            if ($this->$requiredProperty === '') {
                throw CannotStartDump::emptyParameter($requiredProperty);
            }
        }
    }

    public function setCollection(string $collection): static
    {
        $this->collection = $collection;

        return $this;
    }

    public function setAuthenticationDatabase(string $authenticationDatabase): static
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

        $command[] = "--host {$this->host}";
        $command[] = "--port {$this->port}";

        if ($this->collection !== null) {
            $command[] = "--collection {$this->collection}";
        }

        if ($this->authenticationDatabase) {
            $command[] = "--authenticationDatabase {$this->authenticationDatabase}";
        }

        return $this->redirectCommandOutput(implode(' ', $command), $filename);
    }

    public function getProcess(string $dumpFile): Process
    {
        $command = $this->getDumpCommand($dumpFile);

        return Process::fromShellCommandline($command, null, null, null, $this->timeout);
    }
}
