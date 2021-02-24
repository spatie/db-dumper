<?php

namespace Spatie\DbDumper\Databases;

use Spatie\DbDumper\DbDumper;
use Spatie\DbDumper\Exceptions\CannotStartDump;
use Symfony\Component\Process\Process;

class MySql extends DbDumper
{
    /** @var bool */
    protected $skipComments = true;

    /** @var bool */
    protected $useExtendedInserts = true;

    /** @var bool */
    protected $useSingleTransaction = false;

    /** @var bool */
    protected $skipLockTables = false;

    /** @var bool */
    protected $doNotUseColumnStatistics = false;

    /** @var bool */
    protected $useQuick = false;

    /** @var string */
    protected $defaultCharacterSet = '';

    /** @var bool */
    protected $dbNameWasSetAsExtraOption = false;

    /** @var bool */
    protected $allDatabasesWasSetAsExtraOption = false;

    /** @var string */
    protected $setGtidPurged = 'AUTO';

    /** @var bool */
    protected $createTables = true;

    /** @var false|resource */
    private $tempFileHandle;

    public function __construct()
    {
        $this->port = 3306;
    }

    /**
     * @return $this
     */
    public function skipComments()
    {
        $this->skipComments = true;

        return $this;
    }

    /**
     * @return $this
     */
    public function dontSkipComments()
    {
        $this->skipComments = false;

        return $this;
    }

    /**
     * @return $this
     */
    public function useExtendedInserts()
    {
        $this->useExtendedInserts = true;

        return $this;
    }

    /**
     * @return $this
     */
    public function dontUseExtendedInserts()
    {
        $this->useExtendedInserts = false;

        return $this;
    }

    /**
     * @return $this
     */
    public function useSingleTransaction()
    {
        $this->useSingleTransaction = true;

        return $this;
    }

    /**
     * @return $this
     */
    public function dontUseSingleTransaction()
    {
        $this->useSingleTransaction = false;

        return $this;
    }

    /**
     * @return $this
     */
    public function skipLockTables()
    {
        $this->skipLockTables = true;

        return $this;
    }

    /**
     * @return $this
     */
    public function doNotUseColumnStatistics()
    {
        $this->doNotUseColumnStatistics = true;

        return $this;
    }

    /**
     * @return $this
     */
    public function dontSkipLockTables()
    {
        $this->skipLockTables = false;

        return $this;
    }

    /**
     * @return $this
     */
    public function useQuick()
    {
        $this->useQuick = true;

        return $this;
    }

    /**
     * @return $this
     */
    public function dontUseQuick()
    {
        $this->useQuick = false;

        return $this;
    }

    /**
     * @param string $characterSet
     *
     * @return $this
     */
    public function setDefaultCharacterSet(string $characterSet)
    {
        $this->defaultCharacterSet = $characterSet;

        return $this;
    }

    /**
     * @return $this
     */
    public function setGtidPurged(string $setGtidPurged)
    {
        $this->setGtidPurged = $setGtidPurged;

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

    public function addExtraOption(string $extraOption)
    {
        if (strpos($extraOption, '--all-databases') !== false) {
            $this->dbNameWasSetAsExtraOption = true;
            $this->allDatabasesWasSetAsExtraOption = true;
        }

        if (preg_match('/^--databases (\S+)/', $extraOption, $matches) === 1) {
            $this->setDbName($matches[1]);
            $this->dbNameWasSetAsExtraOption = true;
        }

        return parent::addExtraOption($extraOption);
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
     * Get the command that should be performed to dump the database.
     *
     * @param string $dumpFile
     * @param string $temporaryCredentialsFile
     *
     * @return string
     */
    public function getDumpCommand(string $dumpFile, string $temporaryCredentialsFile): string
    {
        $quote = $this->determineQuote();

        $command = [
            "{$quote}{$this->dumpBinaryPath}mysqldump{$quote}",
            "--defaults-extra-file=\"{$temporaryCredentialsFile}\"",
        ];

        if (! $this->createTables) {
            $command[] = '--no-create-info';
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
            $command[] = '--default-character-set='.$this->defaultCharacterSet;
        }

        foreach ($this->extraOptions as $extraOption) {
            $command[] = $extraOption;
        }

        if ($this->setGtidPurged !== 'AUTO') {
            $command[] = '--set-gtid-purged='.$this->setGtidPurged;
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

        return $this->echoToFile(implode(' ', $command), $dumpFile);
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

        return implode(PHP_EOL, $contents);
    }

    public function guardAgainstIncompleteCredentials()
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
