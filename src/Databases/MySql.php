<?php

namespace Spatie\DbDumper\Databases;

use Spatie\DbDumper\DbDumper;
use Spatie\DbDumper\Exceptions\CannotStartDump;
use Symfony\Component\Process\Process;

class MySql extends DbDumper
{
    /** @var bool */
    protected $useExtendedInserts = true;

    /** @var bool */
    protected $useSingleTransaction = false;

    /** @var bool */
    protected $useSkipComments = true;

    public function __construct()
    {
        $this->port = 3306;
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
    public function useSkipComments()
    {
        $this->useSkipComments = true;

        return $this;
    }

    /**
     * @return $this
     */
    public function dontUseSkipComments()
    {
        $this->useSkipComments = false;

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
        fwrite($tempFileHandle, $this->getContentsOfCredentialsFile());
        $temporaryCredentialsFile = stream_get_meta_data($tempFileHandle)['uri'];

        $command = $this->getDumpCommand($dumpFile, $temporaryCredentialsFile);

        $process = new Process($command);

        if (! is_null($this->timeout)) {
            $process->setTimeout($this->timeout);
        }

        $process->run();

        $this->checkIfDumpWasSuccessFul($process, $dumpFile);
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
        /* if call is made from MySqlTest */
        if ($temporaryCredentialsFile == 'credentials.txt') {
            $quote = '"';
        } else {
            $quote = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? '"' : "'");
        }

        $command = [];

        $command[] = $quote . $this->dumpBinaryPath . 'mysqldump' . $quote;
        $command[] = '--defaults-extra-file="' . $temporaryCredentialsFile . '"';
        $command[] = $this->useExtendedInserts ? '--extended-insert' : '--skip-extended-insert';

        if ($this->useSkipComments) {
            $command[] = '--skip-comments';
        }

        if ($this->useSingleTransaction) {
            $command[] = '--single-transaction';
        }

        if ($this->socket !== '') {
            $command[] = '--socket=' . $this->socket;
        }

        if (! empty($this->excludeTables)) {
            $command[] = '--ignore-table='.implode(' --ignore-table=', $this->excludeTables);
        }

        foreach ($this->extraOptions as $extraOption) {
            $command[] = $extraOption;
        }

        $command[] = "{$this->dbName}";

        if (! empty($this->includeTables)) {
            $command[] = implode(' ', $this->includeTables);
        }

        $command[] = "> \"{$dumpFile}\"";

        return implode(' ', $command);
    }

    public function getContentsOfCredentialsFile(): string
    {
        $contents = [
            '[client]',
            "user = '{$this->userName}'",
            "password = '{$this->password}'",
            "host = '{$this->host}'",
            "port = '{$this->port}'",
        ];

        return implode(PHP_EOL, $contents);
    }

    protected function guardAgainstIncompleteCredentials()
    {
        foreach (['userName', 'dbName', 'host'] as $requiredProperty) {
            if (strlen($this->$requiredProperty) === 0) {
                throw CannotStartDump::emptyParameter($requiredProperty);
            }
        }
    }
}
