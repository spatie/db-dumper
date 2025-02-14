<?php

namespace Spatie\DbDumper\Databases;

use Spatie\DbDumper\DbDumper;
use Spatie\DbDumper\Exceptions\CannotSetParameter;
use Spatie\DbDumper\Exceptions\CannotStartDump;
use Symfony\Component\Process\Process;

class MySql extends DbDumper
{
    protected bool $skipSsl = false;

    protected bool $skipComments = true;

    protected bool $useExtendedInserts = true;

    protected bool $useSingleTransaction = false;

    protected bool $skipLockTables = false;

    protected bool $doNotUseColumnStatistics = false;

    protected bool $useQuick = false;

    protected string $defaultCharacterSet = '';

    protected bool $dbNameWasSetAsExtraOption = false;

    protected bool $allDatabasesWasSetAsExtraOption = false;

    protected string $setGtidPurged = 'AUTO';

    protected bool $skipAutoIncrement = false;

    protected bool $createTables = true;

    protected bool $includeData = true;

    /** @var false|resource */
    private $tempFileHandle;

    public function __construct()
    {
        $this->port = 3306;
    }

    public function setSkipSsl(bool $skipSsl = true): self
    {
        $this->skipSsl = $skipSsl;

        return $this;
    }

    public function skipComments(): self
    {
        $this->skipComments = true;

        return $this;
    }

    public function dontSkipComments(): self
    {
        $this->skipComments = false;

        return $this;
    }

    public function useExtendedInserts(): self
    {
        $this->useExtendedInserts = true;

        return $this;
    }

    public function dontUseExtendedInserts(): self
    {
        $this->useExtendedInserts = false;

        return $this;
    }

    public function useSingleTransaction(): self
    {
        $this->useSingleTransaction = true;

        return $this;
    }

    public function dontUseSingleTransaction(): self
    {
        $this->useSingleTransaction = false;

        return $this;
    }

    public function skipLockTables(): self
    {
        $this->skipLockTables = true;

        return $this;
    }

    public function doNotUseColumnStatistics(): self
    {
        $this->doNotUseColumnStatistics = true;

        return $this;
    }

    public function dontSkipLockTables(): self
    {
        $this->skipLockTables = false;

        return $this;
    }

    public function useQuick(): self
    {
        $this->useQuick = true;

        return $this;
    }

    public function dontUseQuick(): self
    {
        $this->useQuick = false;

        return $this;
    }

    public function setDefaultCharacterSet(string $characterSet): self
    {
        $this->defaultCharacterSet = $characterSet;

        return $this;
    }

    public function setGtidPurged(string $setGtidPurged): self
    {
        $this->setGtidPurged = $setGtidPurged;

        return $this;
    }

    public function skipAutoIncrement(): self
    {
        $this->skipAutoIncrement = true;

        return $this;
    }

    public function dontSkipAutoIncrement(): self
    {
        $this->skipAutoIncrement = false;

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

    public function addExtraOption(string $extraOption): self
    {
        if (str_contains($extraOption, '--all-databases')) {
            $this->dbNameWasSetAsExtraOption = true;
            $this->allDatabasesWasSetAsExtraOption = true;
        }

        if (preg_match('/^--databases (\S+)/', $extraOption, $matches) === 1) {
            $this->setDbName($matches[1]);
            $this->dbNameWasSetAsExtraOption = true;
        }

        return parent::addExtraOption($extraOption);
    }

    public function doNotCreateTables(): self
    {
        $this->createTables = false;

        return $this;
    }

    public function doNotDumpData(): self
    {
        $this->includeData = false;

        return $this;
    }

    public function useAppendMode(): self
    {
        if ($this->compressor) {
            throw CannotSetParameter::conflictingParameters('append mode', 'compress');
        }

        $this->appendMode = true;

        return $this;
    }

    public function getDumpCommand(string $dumpFile, string $temporaryCredentialsFile): string
    {
        $quote = $this->determineQuote();

        $command = [
            "{$quote}{$this->dumpBinaryPath}mysqldump{$quote}",
            "--defaults-extra-file=\"{$temporaryCredentialsFile}\"",
        ];
        $finalDumpCommand = $this->getCommonDumpCommand($command);

        return $this->echoToFile($finalDumpCommand, $dumpFile);
    }

    public function getCommonDumpCommand(array $command): string
    {
        if (! $this->createTables) {
            $command[] = '--no-create-info';
        }

        if (! $this->includeData) {
            $command[] = '--no-data';
        }

        if ($this->skipComments) {
            $command[] = '--skip-comments';
        }

        $command[] = $this->useExtendedInserts ? '--extended-insert' : '--skip-extended-insert';

        if ($this->useSingleTransaction) {
            $command[] = '--single-transaction';
        }

        if ($this->skipLockTables) {
            $command[] = '--skip-lock-tables';
        }

        if ($this->doNotUseColumnStatistics) {
            $command[] = '--column-statistics=0';
        }

        if ($this->useQuick) {
            $command[] = '--quick';
        }

        if ($this->socket !== '') {
            $command[] = "--socket={$this->socket}";
        }

        foreach ($this->excludeTables as $tableName) {
            $command[] = "--ignore-table={$this->dbName}.{$tableName}";
        }

        if (! empty($this->defaultCharacterSet)) {
            $command[] = '--default-character-set=' . $this->defaultCharacterSet;
        }

        foreach ($this->extraOptions as $extraOption) {
            $command[] = $extraOption;
        }

        if ($this->setGtidPurged !== 'AUTO') {
            $command[] = '--set-gtid-purged=' . $this->setGtidPurged;
        }

        if (! $this->dbNameWasSetAsExtraOption) {
            $command[] = $this->dbName;
        }

        if (! empty($this->includeTables)) {
            $includeTables = implode(' ', $this->includeTables);
            $command[] = "--tables {$includeTables}";
        }

        foreach ($this->extraOptionsAfterDbName as $extraOptionAfterDbName) {
            $command[] = $extraOptionAfterDbName;
        }

        $finalDumpCommand = implode(' ', $command);

        if ($this->skipAutoIncrement) {
            $sedCommand = "sed 's/ AUTO_INCREMENT=[0-9]*\b//'";
            $finalDumpCommand .= " | {$sedCommand}";
        }

        return $finalDumpCommand;
    }

    public function getContentsOfCredentialsFile(): string
    {
        $contents = [
            '[client]',
            "user = '{$this->userName}'",
            "password = '{$this->password}'",
            "port = '{$this->port}'",
        ];

        if ($this->socket === '') {
            $contents[] = "host = '{$this->host}'";
        }

        if ($this->skipSsl) {
            $contents[] = "skip-ssl";
        }

        return implode(PHP_EOL, $contents);
    }

    public function guardAgainstIncompleteCredentials(): void
    {
        foreach (['userName', 'host'] as $requiredProperty) {
            if (strlen($this->$requiredProperty) === 0) {
                throw CannotStartDump::emptyParameter($requiredProperty);
            }
        }

        if (strlen($this->dbName) === 0 && ! $this->allDatabasesWasSetAsExtraOption) {
            throw CannotStartDump::emptyParameter('dbName');
        }
    }

    /**
     * @param string $dumpFile
     * @return Process
     */
    public function getProcess(string $dumpFile): Process
    {
        fwrite($this->getTempFileHandle(), $this->getContentsOfCredentialsFile());
        $temporaryCredentialsFile = stream_get_meta_data($this->getTempFileHandle())['uri'];

        $command = $this->getDumpCommand($dumpFile, $temporaryCredentialsFile);

        return Process::fromShellCommandline($command, null, null, null, $this->timeout);
    }

    /**
     * @return false|resource
     */
    public function getTempFileHandle(): mixed
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
