<?php

namespace Spatie\DbDumper;

use Spatie\DbDumper\Compressors\Compressor;
use Spatie\DbDumper\Exceptions\CannotSetParameter;
use Spatie\DbDumper\Exceptions\DumpFailed;
use Symfony\Component\Process\Process;

abstract class DbDumper
{
    protected string $dbName = '';

    protected string $userName = '';

    protected string $password = '';

    protected string $host = 'localhost';

    protected int $port = 5432;

    protected string $socket = '';

    protected int $timeout = 0;

    protected string $dumpBinaryPath = '';

    protected array $includeTables = [];

    protected array $excludeTables = [];

    protected array $extraOptions = [];

    protected array $extraOptionsAfterDbName = [];

    protected ?object $compressor = null;

    public static function create(): static
    {
        return new static();
    }

    public function getDbName(): string
    {
        return $this->dbName;
    }

    public function setDbName(string $dbName): static
    {
        $this->dbName = $dbName;

        return $this;
    }

    public function setUserName(string $userName): static
    {
        $this->userName = $userName;

        return $this;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function setHost(string $host): static
    {
        $this->host = $host;

        return $this;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function setPort(int $port): static
    {
        $this->port = $port;

        return $this;
    }

    public function setSocket(string $socket): static
    {
        $this->socket = $socket;

        return $this;
    }

    public function setTimeout(int $timeout): static
    {
        $this->timeout = $timeout;

        return $this;
    }

    public function setDumpBinaryPath(string $dumpBinaryPath): static
    {
        if ($dumpBinaryPath !== '' && ! str_ends_with($dumpBinaryPath, '/')) {
            $dumpBinaryPath .= '/';
        }

        $this->dumpBinaryPath = $dumpBinaryPath;

        return $this;
    }

    public function getCompressorExtension(): string
    {
        return $this->compressor->useExtension();
    }

    public function useCompressor(Compressor $compressor): static
    {
        $this->compressor = $compressor;

        return $this;
    }

    public function includeTables($includeTables): static
    {
        if (! empty($this->excludeTables)) {
            throw CannotSetParameter::conflictingParameters('includeTables', 'excludeTables');
        }

        if (! is_array($includeTables)) {
            $includeTables = explode(', ', $includeTables);
        }

        $this->includeTables = $includeTables;

        return $this;
    }

    public function excludeTables($excludeTables): static
    {
        if (! empty($this->includeTables)) {
            throw CannotSetParameter::conflictingParameters('excludeTables', 'includeTables');
        }

        if (! is_array($excludeTables)) {
            $excludeTables = explode(', ', $excludeTables);
        }

        $this->excludeTables = $excludeTables;

        return $this;
    }

    public function addExtraOption(string $extraOption): static
    {
        if (! empty($extraOption)) {
            $this->extraOptions[] = $extraOption;
        }

        return $this;
    }

    public function addExtraOptionAfterDbName(string $extraOptionAfterDbName): static
    {
        if (! empty($extraOptionAfterDbName)) {
            $this->extraOptionsAfterDbName[] = $extraOptionAfterDbName;
        }

        return $this;
    }

    abstract public function dumpToFile(string $dumpFile): void;

    public function checkIfDumpWasSuccessFul(Process $process, string $outputFile): void
    {
        if (! $process->isSuccessful()) {
            throw DumpFailed::processDidNotEndSuccessfully($process);
        }

        if (! file_exists($outputFile)) {
            throw DumpFailed::dumpfileWasNotCreated($process);
        }

        if (filesize($outputFile) === 0) {
            throw DumpFailed::dumpfileWasEmpty($process);
        }
    }

    protected function getCompressCommand(string $command, string $dumpFile): string
    {
        $compressCommand = $this->compressor->useCommand();

        if ($this->isWindows()) {
            return "{$command} | {$compressCommand} > {$dumpFile}";
        }

        return "(((({$command}; echo \$? >&3) | {$compressCommand} > {$dumpFile}) 3>&1) | (read x; exit \$x))";
    }

    protected function echoToFile(string $command, string $dumpFile): string
    {
        $dumpFile = '"'.addcslashes($dumpFile, '\\"').'"';

        if ($this->compressor) {
            return $this->getCompressCommand($command, $dumpFile);
        }

        return $command.' > '.$dumpFile;
    }

    protected function determineQuote(): string
    {
        return $this->isWindows() ? '"' : "'";
    }

    protected function isWindows(): bool
    {
        return str_starts_with(strtoupper(PHP_OS), 'WIN');
    }
}
