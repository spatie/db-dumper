<?php

namespace Spatie\DbDumper;

use Spatie\DbDumper\Exceptions\InvalidDatabaseUrl;

class DsnParser
{
    public function __construct(protected string $dsn)
    {
    }

    /** @return array<string, mixed> */
    public function parse(): array
    {
        $rawComponents = $this->parseUrl($this->dsn);

        $stringComponents = array_map(strval(...), $rawComponents);

        /** @var array<string, string|int> $decodedComponents */
        $decodedComponents = $this->parseNativeTypes(
            array_map(rawurldecode(...), $stringComponents)
        );

        return array_merge(
            $this->getPrimaryOptions($decodedComponents),
            $this->getQueryOptions($rawComponents)
        );
    }

    /**
     * @param array<string, string|int> $url
     * @return array<string, mixed>
     */
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

    /** @param array<string, string|int> $url */
    protected function getDatabase(array $url): ?string
    {
        $path = (string) ($url['path'] ?? '');

        if ($path === '' || $path === '/') {
            return null;
        }

        $scheme = (string) ($url['scheme'] ?? '');

        if (str_contains($scheme, 'sqlite')) {
            return $path;
        }

        return trim($path, '/');
    }

    /**
     * @param array<string, string|int> $url
     * @return array<string, mixed>
     */
    protected function getQueryOptions(array $url): array
    {
        $queryString = (string) ($url['query'] ?? '');

        if ($queryString === '') {
            return [];
        }

        $query = [];

        parse_str($queryString, $query);

        /** @var array<string, mixed> */
        return $this->parseNativeTypes($query);
    }

    /** @return array<string, string|int> */
    protected function parseUrl(string $url): array
    {
        $url = preg_replace('#^(sqlite3?):///#', '$1://null/', $url) ?? $url;

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
