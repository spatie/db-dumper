<?php

namespace Spatie\DbDumper\Exceptions;

use Exception;
use Symfony\Component\Process\Process;

class DumpFailed extends Exception
{
    public static function processDidNotEndSuccessfully(Process $process): self
    {
        $processOutput = static::formatProcessOutput($process);

        return new self("The dump process failed with a none successful exitcode.{$processOutput}");
    }

    public static function dumpfileWasNotCreated(Process $process): self
    {
        $processOutput = static::formatProcessOutput($process);

        return new self("The dumpfile could not be created.{$processOutput}");
    }

    public static function dumpfileWasEmpty(Process $process): self
    {
        $processOutput = static::formatProcessOutput($process);

        return new self("The created dumpfile is empty.{$processOutput}");
    }

    protected static function formatProcessOutput(Process $process): string
    {
        $output = $process->getOutput() ?: '<no output>';
        $errorOutput = $process->getErrorOutput() ?: '<no output>';
        $exitCodeText = $process->getExitCodeText() ?: '<no exit text>';

        return <<<CONSOLE

            Exitcode
            ========
            {$process->getExitCode()}: {$exitCodeText}

            Output
            ======
            {$output}

            Error Output
            ============
            {$errorOutput}
            CONSOLE;
    }
}
