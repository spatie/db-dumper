<?php

namespace Spatie\DbDumper;

use Spatie\DbDumper\Exceptions\InvalidDatabaseUrl;

class DsnParser
{
    public function __construct(protected string $dsn)
    {
    }

    public function parse(): array
    {
        $rawComponents = $this->parseUrl($this->dsn);

        $decodedComponents = $this->parseNativeTypes(
            array_map('rawurldecode', $rawComponents)
        );

        return array_merge(
            $this->getPrimaryOptions($decodedComponents),
            $this->getQueryOptions($rawComponents)
        );
    }

    protected function getPrimaryOptions(array $url): array
    {
        return array_filter([
            'database' => $this->getDatabase($url),
            'host' => $url['host'] ?? null,
            'port' => $url['port'] ?? null,
            'username' => $url['user'] ?? null,
            'password' => $url['pass'] ?? null,
        ], static fn ($value) => ! is_null($value));
    }

    protected function getDatabase(array $url): ?string
    {
        $path = $url['path'] ?? null;

        if (! $path) {
            return null;
        }

        if ($path === '/') {
            return null;
        }

        if (isset($url['scheme']) && str_contains($url['scheme'], 'sqlite')) {
            return $path;
        }

        return trim($path, '/');
    }

    protected function getQueryOptions(array $url): array
    {
        $queryString = $url['query'] ?? null;

        if (! $queryString) {
            return [];
        }

        $query = [];

        parse_str($queryString, $query);

        return $this->parseNativeTypes($query);
    }

    protected function parseUrl(string $url): array
    {
        $url = preg_replace('#^(sqlite3?):///#', '$1://null/', $url);

        $parsedUrl = parse_url($url);

        if ($parsedUrl === false) {
            throw InvalidDatabaseUrl::invalidUrl($url);
        }

        return $parsedUrl;
    }

    protected function parseNativeTypes(mixed $value): mixed
    {
        if (is_array($value)) {
            return array_map([$this, 'parseNativeTypes'], $value);
        }

        if (! is_string($value)) {
            return $value;
        }

        $parsedValue = json_decode($value, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            return $parsedValue;
        }

        return $value;
    }
}
