<?php

namespace Spatie\DbDumper\Databases;

class MariaDb extends MySql
{
    protected string $sslFlag = 'skip-ssl';

    protected bool $withSandboxMode = true;

    public function getDumpCommand(string $dumpFile, string $temporaryCredentialsFile): string
    {
        $quote = $this->determineQuote();

        $command = [
            "{$quote}{$this->dumpBinaryPath}mariadb-dump{$quote}",
            "--defaults-extra-file=\"{$temporaryCredentialsFile}\"",
        ];

        $finalDumpCommand = $this->getCommonDumpCommand($command) . $this->determineSandboxMode();

        return $this->echoToFile($finalDumpCommand, $dumpFile);
    }

    public function withoutSandboxMode(): self
    {
        $this->withSandboxMode = false;

        return $this;
    }

    public function determineSandboxMode(): string
    {
        // allow mariadb/MySQL compatability: https://mariadb.org/mariadb-dump-file-compatibility-change/
        return $this->withSandboxMode ? '' : '|tail +2';
    }
}
