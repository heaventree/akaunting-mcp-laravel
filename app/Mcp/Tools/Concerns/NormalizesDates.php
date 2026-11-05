<?php

namespace App\Mcp\Tools\Concerns;

trait NormalizesDates
{
    /**
     * Akaunting validates transaction dates as Y-m-d H:i:s exactly. Accepts a
     * bare date, a datetime, or an ISO-8601 string as returned by the API.
     */
    protected function toDateTime(string $value): string
    {
        $value = trim($value);

        if (strlen($value) === 10) {
            return $value.' 00:00:00';
        }

        if (str_contains($value, 'T')) {
            return substr(str_replace('T', ' ', $value), 0, 19);
        }

        return $value;
    }

    protected function toDate(string $value): string
    {
        return substr(trim($value), 0, 10);
    }

    /**
     * Unwrap Akaunting's {"data": {...}} envelope.
     *
     * @return array<string, mixed>
     */
    protected function unwrap(mixed $response): array
    {
        return is_array($response) && isset($response['data']) && is_array($response['data'])
            ? $response['data']
            : (array) $response;
    }
}
