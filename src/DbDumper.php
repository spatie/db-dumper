<?php

namespace Spatie\DbDumper;

use Spatie\DbDumper\Exceptions\DumpFailed;
use Symfony\Component\Process\Process;

abstract class DbDumper
{
    /**
     * @var \Symfony\Component\Process\Process
     */
    protected $process;

    public function __construct(Process $process)
    {
        $this->process = $process;
    }

    public static function create()
    {
        return new static(new Process(''));
    }

    protected function checkIfDumpWasSuccessFull(string $outputFile) : bool
    {
        if (!$this->process->isSuccessful()) {
            throw DumpFailed::processDidNotEndSuccessfully($this->process);
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
