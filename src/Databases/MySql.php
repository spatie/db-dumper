<?php

namespace Spatie\DbDumper\Databases;

use Illuminate\Support\Facades\DB;
use Spatie\DbDumper\DbDumper;
use Spatie\DbDumper\Exceptions\CannotSetParameter;
use Spatie\DbDumper\Exceptions\CannotStartDump;
use Symfony\Component\Process\Process;

class MySql extends DbDumper
{
    protected bool $skipSsl = false;

    protected string $sslFlag = '';

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

    public function __construct()
    {
        $this->port = 3306;
    }

    public function setSkipSsl(bool $skipSsl = true): static
    {
        $this->skipSsl = $skipSsl;

        return $this;
    }

    public function setSslFlag(string $sslFlag = ''): static
    {
        $allowedValues = [
            'skip-ssl',
            'ssl-mode=DISABLED',
            'ssl-mode=PREFERRED',
        ];

        if (in_array($sslFlag, $allowedValues)) {
            $this->sslFlag = $sslFlag;
        }

        return $this;
    }

    public function skipComments(): static
    {
        $this->skipComments = true;

        return $this;
    }

    public function dontSkipComments(): static
    {
        $this->skipComments = false;

        return $this;
    }

    public function useExtendedInserts(): static
    {
        $this->useExtendedInserts = true;

        return $this;
    }

    public function dontUseExtendedInserts(): static
    {
        $this->useExtendedInserts = false;

        return $this;
    }

    public function useSingleTransaction(): static
    {
        $this->useSingleTransaction = true;

        return $this;
    }

    public function dontUseSingleTransaction(): static
    {
        $this->useSingleTransaction = false;

        return $this;
    }

    public function skipLockTables(): static
    {
        $this->skipLockTables = true;

        return $this;
    }

    public function doNotUseColumnStatistics(): static
    {
        $this->doNotUseColumnStatistics = true;

        return $this;
    }

    public function dontSkipLockTables(): static
    {
        $this->skipLockTables = false;

        return $this;
    }

    public function useQuick(): static
    {
        $this->useQuick = true;

        return $this;
    }

    public function dontUseQuick(): static
    {
        $this->useQuick = false;

        return $this;
    }

    public function setDefaultCharacterSet(string $characterSet): static
    {
        $this->defaultCharacterSet = $characterSet;

        return $this;
    }

    public function setGtidPurged(string $setGtidPurged): static
    {
        $this->setGtidPurged = $setGtidPurged;

        return $this;
    }

    public function skipAutoIncrement(): static
    {
        $this->skipAutoIncrement = true;

        return $this;
    }

    public function dontSkipAutoIncrement(): static
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

    public function addExtraOption(string $extraOption): static
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

    public function useAppendMode(): static
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
            $contents[] = $this->getSSLFlag();
        }

        return implode(PHP_EOL, $contents);
    }

    public function guardAgainstIncompleteCredentials(): void
    {
        foreach (['userName', 'host'] as $requiredProperty) {
            if ($this->$requiredProperty === '') {
                throw CannotStartDump::emptyParameter($requiredProperty);
            }
        }

        if ($this->dbName === '' && ! $this->allDatabasesWasSetAsExtraOption) {
            throw CannotStartDump::emptyParameter('dbName');
        }
    }

    public function getProcess(string $dumpFile): Process
    {
        fwrite($this->getTempFileHandle(), $this->getContentsOfCredentialsFile());
        $temporaryCredentialsFile = stream_get_meta_data($this->getTempFileHandle())['uri'];

        $command = $this->getDumpCommand($dumpFile, $temporaryCredentialsFile);

        return Process::fromShellCommandline($command, null, null, null, $this->timeout);
    }

    /**
     * Since MySQL 8.0.26, --skip-ssl has been deprecated and replaced with ssl-mode=DISABLED.
     * Since MySQL 8.4.0, --skip-ssl has been removed.
     *
     * https://dev.mysql.com/doc/relnotes/mysql/8.4/en/news-8-4-0.html
     */
    protected function getSSLFlag(): string
    {
        if ($this->sslFlag !== '') {
            return $this->sslFlag;
        }

        $sslFlag = 'skip-ssl';
        $mysqlVersion = DB::selectOne('SELECT VERSION() AS version');

        if (version_compare($mysqlVersion->version, '8.4.0', '>=')) {
            $sslFlag = 'ssl-mode=DISABLED';
        }

        return $sslFlag;
    }
}
