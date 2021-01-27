<?php

namespace Spatie\DbDumper\Databases;

use Spatie\DbDumper\DbDumper;
use Symfony\Component\Process\Process;

class Sqlite extends DbDumper
{
    /**
     * Dump the contents of the database to a given file.
     *
     * @param string $dumpFile
     *
     * @throws \Spatie\DbDumper\Exceptions\DumpFailed
     */
    public function dumpToFile(string $dumpFile)
    {
        $process = $this->getProcess($dumpFile);

        $process->run();

        $this->checkIfDumpWasSuccessFul($process, $dumpFile);
    }

    /**
     * Get the command that should be performed to dump the database.
     *
     * @param string $dumpFile
     *
     * @return string
     */
    public function getDumpCommand(string $dumpFile): string
    {
        $dumpInSqlite = "echo 'BEGIN IMMEDIATE;\n.dump'";
        if ($this->isWindows()) {
            $dumpInSqlite = '(echo BEGIN IMMEDIATE; & echo .dump)';
        }
        $quote = $this->determineQuote();

        $command = sprintf(
            "{$dumpInSqlite} | {$quote}%ssqlite3{$quote} --bail {$quote}%s{$quote}",
            $this->dumpBinaryPath,
            $this->dbName
        );

        return $this->echoToFile($command, $dumpFile);
    }

    /**
     * @param string $dumpFile
     * @return Process
     */
    public function getProcess(string $dumpFile): Process
    {
        $command = $this->getDumpCommand($dumpFile);

        return Process::fromShellCommandline($command, null, null, null, $this->timeout);
    }
}
