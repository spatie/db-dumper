<?php

namespace Spatie\DbDumper\Exceptions;

use Exception;
use Symfony\Component\Process\Process;

class DumpFailed extends Exception
{
    public static function processDidNotEndSuccessfully(Process $process)
    {
        return new static("The dump process failed with exitcode {$process->getExitCode()} : {$process->getExitCodeText()} : {$process->getErrorOutput()}");
    }

    public static function dumpfileWasNotCreated()
    {
        return new static('The dumpfile could not be created');
    }

    public static function dumpfileWasEmpty()
    {
        return new static('The created dumpfile is empty');
    }
}
