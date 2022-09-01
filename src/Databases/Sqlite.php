<?php

namespace Spatie\DbDumper\Databases;

use Spatie\DbDumper\DbDumper;
use SQLite3;
use Symfony\Component\Process\Process;

class Sqlite extends DbDumper
{
    public function dumpToFile(string $dumpFile): void
    {
        $process = $this->getProcess($dumpFile);

        $process->run();

        $this->checkIfDumpWasSuccessFul($process, $dumpFile);
    }

    public function getDbTables(): array
    {
        $db = new SQLite3($this->dbName);
        $query = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%';");
        $tables = [];
        while ($table = $query->fetchArray(SQLITE3_ASSOC)) {
            $tables[] = $table['name'];
        }
        $db->close();

        return $tables;
    }

    public function getDumpCommand(string $dumpFile): string
    {
        $includeTables = rtrim(' ' . implode(' ', $this->includeTables));
        if (empty($includeTables) && ! empty($this->excludeTables)) {
            $tables = $this->getDbTables();
            $includeTables = rtrim(' ' . implode(' ', array_diff($tables, $this->excludeTables)));
        }
        $dumpInSqlite = "echo 'BEGIN IMMEDIATE;\n.dump{$includeTables}'";
        if ($this->isWindows()) {
            $dumpInSqlite = "(echo BEGIN IMMEDIATE; & echo .dump{$includeTables})";
        }
        $quote = $this->determineQuote();

        $command = sprintf(
            "{$dumpInSqlite} | {$quote}%ssqlite3{$quote} --bail {$quote}%s{$quote}",
            $this->dumpBinaryPath,
            $this->dbName
        );

        return $this->echoToFile($command, $dumpFile);
    }

    public function getProcess(string $dumpFile): Process
    {
        $command = $this->getDumpCommand($dumpFile);

        return Process::fromShellCommandline($command, null, null, null, $this->timeout);
    }
}
