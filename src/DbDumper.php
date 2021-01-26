<?php

namespace Spatie\DbDumper;

use Spatie\DbDumper\Compressors\Compressor;
use Spatie\DbDumper\Compressors\GzipCompressor;
use Spatie\DbDumper\Exceptions\CannotSetParameter;
use Spatie\DbDumper\Exceptions\DumpFailed;
use Symfony\Component\Process\Process;

abstract class DbDumper
{
    /** @var string */
    protected $dbName;

    /** @var string */
    protected $userName;

    /** @var string */
    protected $password;

    /** @var string */
    protected $host = 'localhost';

    /** @var int */
    protected $port = 5432;

    /** @var string */
    protected $socket = '';

    /** @var int */
    protected $timeout = 0;

    /** @var string */
    protected $dumpBinaryPath = '';

    /** @var array */
    protected $includeTables = [];

    /** @var array */
    protected $excludeTables = [];

    /** @var array */
    protected $extraOptions = [];

    /** @var array */
    protected $extraOptionsAfterDbName = [];

    /** @var object */
    protected $compressor = null;

    public static function create()
    {
        return new static();
    }

    public function getDbName(): string
    {
        return $this->dbName;
    }

    /**
     * @param string $dbName
     *
     * @return $this
     */
    public function setDbName(string $dbName)
    {
        $this->dbName = $dbName;

        return $this;
    }

    /**
     * @param string $userName
     *
     * @return $this
     */
    public function setUserName(string $userName)
    {
        $this->userName = $userName;

        return $this;
    }

    /**
     * @param string $password
     *
     * @return $this
     */
    public function setPassword(string $password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @param string $host
     *
     * @return $this
     */
    public function setHost(string $host)
    {
        $this->host = $host;

        return $this;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @param int $port
     *
     * @return $this
     */
    public function setPort(int $port)
    {
        $this->port = $port;

        return $this;
    }

    /**
     * @param string $socket
     *
     * @return $this
     */
    public function setSocket(string $socket)
    {
        $this->socket = $socket;

        return $this;
    }

    /**
     * @param int $timeout
     *
     * @return $this
     */
    public function setTimeout(int $timeout)
    {
        $this->timeout = $timeout;

        return $this;
    }

    public function setDumpBinaryPath(string $dumpBinaryPath)
    {
        if ($dumpBinaryPath !== '' && substr($dumpBinaryPath, -1) !== '/') {
            $dumpBinaryPath .= '/';
        }

        $this->dumpBinaryPath = $dumpBinaryPath;

        return $this;
    }

    /**
     * @deprecated
     *
     * @return $this
     */
    public function enableCompression()
    {
        $this->compressor = new GzipCompressor();

        return $this;
    }

    public function getCompressorExtension(): string
    {
        return $this->compressor->useExtension();
    }

    public function useCompressor(Compressor $compressor)
    {
        $this->compressor = $compressor;

        return $this;
    }

    /**
     * @param string|array $includeTables
     *
     * @return $this
     *
     * @throws \Spatie\DbDumper\Exceptions\CannotSetParameter
     */
    public function includeTables($includeTables)
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

    /**
     * @param string|array $excludeTables
     *
     * @return $this
     *
     * @throws \Spatie\DbDumper\Exceptions\CannotSetParameter
     */
    public function excludeTables($excludeTables)
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

    /**
     * @param string $extraOption
     *
     * @return $this
     */
    public function addExtraOption(string $extraOption)
    {
        if (! empty($extraOption)) {
            $this->extraOptions[] = $extraOption;
        }

        return $this;
    }

    /**
     * @param string $extraOptionAtEnd
     *
     * @return $this
     */
    public function addExtraOptionAfterDbName(string $extraOptionAfterDbName)
    {
        if (! empty($extraOptionAfterDbName)) {
            $this->extraOptionsAfterDbName[] = $extraOptionAfterDbName;
        }

        return $this;
    }

    abstract public function dumpToFile(string $dumpFile);

    public function checkIfDumpWasSuccessFul(Process $process, string $outputFile)
    {
        if (! $process->isSuccessful()) {
            throw DumpFailed::processDidNotEndSuccessfully($process);
        }

        if (! file_exists($outputFile)) {
            throw DumpFailed::dumpfileWasNotCreated();
        }

        if (filesize($outputFile) === 0) {
            throw DumpFailed::dumpfileWasEmpty();
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
        return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
    }
}
