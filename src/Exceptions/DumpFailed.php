<?php

namespace Spatie\DbDumper\Exceptions;

use Exception;
use Symfony\Component\Process\Process;

class DumpFailed extends Exception
{
    /**
     * @param \Symfony\Component\Process\Process $process
     *
     * @return \Spatie\DbDumper\Exceptions\DumpFailed
     */
    public static function processDidNotEndSuccessfully(Process $process)
    {
        return new static("The dump process failed with exitcode {$process->getExitCode()} : {$process->getExitCodeText()} : {$process->getErrorOutput()}");
    }

    /**
     * @return \Spatie\DbDumper\Exceptions\DumpFailed
     */
    public static function dumpfileWasNotCreated()
    {
        return new static('The dumpfile could not be created');
    }

    /**
     * @return \Spatie\DbDumper\Exceptions\DumpFailed
     */
    public static function dumpfileWasEmpty()
    {
        return new static('The created dumpfile is empty');
    }
}
