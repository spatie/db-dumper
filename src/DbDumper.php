<?php

namespace Spatie\DbDumper;

use Spatie\DbDumper\Compressors\Compressor;
use Spatie\DbDumper\Exceptions\CannotSetParameter;
use Spatie\DbDumper\Exceptions\DumpFailed;
use Symfony\Component\Process\Process;

abstract class DbDumper
{
    protected string $databaseUrl = '';

    protected string $dbName = '';

    protected string $userName = '';

    protected string $password = '';

    protected string $host = 'localhost';

    protected int $port = 5432;

    protected string $socket = '';

    protected int $timeout = 0;

    protected string $dumpBinaryPath = '';

    /** @var array<int, string> */
    protected array $includeTables = [];

    /** @var array<int, string> */
    protected array $excludeTables = [];

    /** @var array<int, string> */
    protected array $extraOptions = [];

    /** @var array<int, string> */
    protected array $extraOptionsAfterDbName = [];

    protected bool $appendMode = false;

    protected bool $createTables = true;

    protected bool $includeData = true;

    protected ?Compressor $compressor = null;

    /** @var false|resource */
    protected mixed $tempFileHandle = false;

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

    public function getDatabaseUrl(): string
    {
        return $this->databaseUrl;
    }

    public function setDatabaseUrl(string $databaseUrl): static
    {
        $this->databaseUrl = $databaseUrl;

        $this->configureFromDatabaseUrl();

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

    public function setDumpBinaryPath(string $dumpBinaryPath = ''): static
    {
        if ($dumpBinaryPath !== '' && ! str_ends_with($dumpBinaryPath, '/')) {
            $dumpBinaryPath .= '/';
        }

        $this->dumpBinaryPath = $dumpBinaryPath;

        return $this;
    }

    public function getCompressorExtension(): string
    {
        return $this->compressor?->useExtension() ?? '';
    }

    public function useCompressor(Compressor $compressor): static
    {
        if ($this->appendMode) {
            throw CannotSetParameter::conflictingParameters('compressor', 'append mode');
        }

        $this->compressor = $compressor;

        return $this;
    }

    /** @param string|array<int, string> $includeTables */
    public function includeTables(string|array $includeTables): static
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

    /** @param string|array<int, string> $excludeTables */
    public function excludeTables(string|array $excludeTables): static
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
        if ($extraOption !== '') {
            $this->extraOptions[] = $extraOption;
        }

        return $this;
    }

    public function addExtraOptionAfterDbName(string $extraOptionAfterDbName): static
    {
        if ($extraOptionAfterDbName !== '') {
            $this->extraOptionsAfterDbName[] = $extraOptionAfterDbName;
        }

        return $this;
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

    abstract public function dumpToFile(string $dumpFile): void;

    public function checkIfDumpWasSuccessful(Process $process, string $outputFile): void
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

    /** @return false|resource */
    public function getTempFileHandle(): mixed
    {
        return $this->tempFileHandle;
    }

    /** @param false|resource $tempFileHandle */
    public function setTempFileHandle(mixed $tempFileHandle): void
    {
        $this->tempFileHandle = $tempFileHandle;
    }

    protected function configureFromDatabaseUrl(): void
    {
        $parsed = (new DsnParser($this->databaseUrl))->parse();

        $componentMap = [
            'host' => 'setHost',
            'port' => 'setPort',
            'database' => 'setDbName',
            'username' => 'setUserName',
            'password' => 'setPassword',
        ];

        foreach ($parsed as $component => $value) {
            if (isset($componentMap[$component])) {
                $setterMethod = $componentMap[$component];

                if (empty($value) || $value === 'null') {
                    continue;
                }

                if ($setterMethod === 'setPort') {
                    $this->setPort(is_numeric($value) ? (int) $value : 0);
                } else {
                    $stringValue = is_scalar($value) ? (string) $value : '';
                    $this->$setterMethod($stringValue);
                }
            }
        }
    }

    protected function getCompressCommand(string $command, string $dumpFile): string
    {
        $compressCommand = $this->compressor?->useCommand() ?? '';

        if ($this->isWindows()) {
            return "{$command} | {$compressCommand} > {$dumpFile}";
        }

        return "(((({$command}; echo \$? >&3) | {$compressCommand} > {$dumpFile}) 3>&1) | (read x; exit \$x))";
    }

    protected function redirectCommandOutput(string $command, string $dumpFile): string
    {
        $dumpFile = '"' . addcslashes($dumpFile, '\\"') . '"';

        if ($this->compressor) {
            return $this->getCompressCommand($command, $dumpFile);
        }

        if ($this->appendMode) {
            return $command . ' >> ' . $dumpFile;
        }

        return $command . ' > ' . $dumpFile;
    }

    protected function determineQuote(): string
    {
        return $this->isWindows() ? '"' : "'";
    }

    protected function isWindows(): bool
    {
        return PHP_OS_FAMILY === 'Windows';
    }
}
