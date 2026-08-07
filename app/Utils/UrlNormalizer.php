<?php

declare(strict_types=1);

namespace App\Utils;

final readonly class UrlNormalizer
{
    private function __construct()
    {
        // static class
    }

    /**
     * Normalizes a URL.
     */
    public static function normalize(string $url): string
    {
        $url = trim($url);

        if ($url === '') {
            return '';
        }

        $parsed = parse_url($url);

        if ($parsed === false) {
            return $url;
        }

        $scheme = $parsed['scheme'] ?? null;
        $host = $parsed['host'] ?? null;
        $port = $parsed['port'] ?? null;
        $path = preg_replace(
            pattern: '~//+~',
            replacement: '/',
            subject: $parsed['path'] ?? ''
        ) ?? '';

        $normalized = '';

        if ($scheme !== null) {
            $normalized .= $scheme . '://';
        }

        if ($host !== null) {
            $normalized .= $host;
        }

        if ($port !== null) {
            $normalized .= ':' . $port;
        }

        $normalized .= '/' . ltrim($path, '/');

        if (isset($parsed['query'])) {
            $normalized .= '?' . $parsed['query'];
        }

        if (isset($parsed['fragment'])) {
            $normalized .= '#' . $parsed['fragment'];
        }

        return $normalized;
    }
}
