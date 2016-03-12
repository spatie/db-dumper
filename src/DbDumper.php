<?php

namespace Spatie\DbDumper;

use Spatie\DbDumper\Exceptions\DumpFailed;
use Symfony\Component\Process\Process;

abstract class DbDumper
{
    public static function create()
    {
        return new static();
    }

    /**
     * Dump the contents of the database to the given file.
     *
     * @param string $dumpFile
     */
    abstract public function dumpToFile($dumpFile);

    /**
     * @return string
     */
    abstract public function getDbName();

    /**
     * @param \Symfony\Component\Process\Process $process
     * @param string                             $outputFile
     *
     * @return bool
     *
     * @throws \Spatie\DbDumper\Exceptions\DumpFailed
     */
    protected function checkIfDumpWasSuccessFul(Process $process, $outputFile)
    {
        if (!$process->isSuccessful()) {
            throw DumpFailed::processDidNotEndSuccessfully($process);
        }

        if (!file_exists($outputFile)) {
            throw DumpFailed::dumpfileWasNotCreated();
        }

        if (filesize($outputFile) === 0) {
            throw DumpFailed::dumpfileWasEmpty();
        }

        return true;
    }
}
