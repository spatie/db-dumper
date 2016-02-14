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

    /*
    * Dump the contents of the database to the given file.
    */
    abstract public function dumpToFile(string $dumpFile);

    abstract public function getDbName() : string;

    protected function checkIfDumpWasSuccessFul(Process $process, string $outputFile) : bool
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
