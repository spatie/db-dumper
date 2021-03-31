<?php

namespace Spatie\DbDumper\Exceptions;

use Exception;
use Symfony\Component\Process\Process;

class DumpFailed extends Exception
{
    public static function processDidNotEndSuccessfully(Process $process): static
    {
        return new static("The dump process failed with exitcode {$process->getExitCode()} : {$process->getExitCodeText()} : {$process->getErrorOutput()}");
    }

    public static function dumpfileWasNotCreated(): static
    {
        return new static('The dumpfile could not be created');
    }

    public static function dumpfileWasEmpty(): static
    {
        return new static('The created dumpfile is empty');
    }
}
