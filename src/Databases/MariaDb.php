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

        return $this->redirectCommandOutput($finalDumpCommand, $dumpFile);
    }

    public function withoutSandboxMode(): static
    {
        $this->withSandboxMode = false;

        return $this;
    }

    protected function determineSandboxMode(): string
    {
        // allow mariadb/MySQL compatability: https://mariadb.org/mariadb-dump-file-compatibility-change/
        return $this->withSandboxMode ? '' : '|tail +2';
    }
}
