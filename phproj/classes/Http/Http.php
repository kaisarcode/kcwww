<?php
/**
 * Http - HTTP utilities
 * Summary: Provides utilities for HTTP operations including headers, requests, routing, and response handling
 *
 * Author:  KaisarCode
 * Website: https://kaisarcode.com
 * License: GNU GPL v3.0
 * License URL: https://www.gnu.org/licenses/gpl-3.0.html
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License.
 */

/**
 * HTTP utilities
 */
class Http
{

    /**
     * Allow cross-origin requests
     *
     * @param string $origin
     */
    static function allowOrigin(string $origin = '*'): void
    {
        header("Access-Control-Allow-Origin: $origin");
        header('Access-Control-Allow-Headers: Content-Type');
    }

    /**
     * Set response header to JSON
     *
     * @param string $charset
     */
    static function setHeaderJson(string $charset = 'utf-8')
    {
        header("Content-Type: application/json; charset=$charset");
    }

    /**
     * Set response header to HTML
     *
     * @param string $charset
     */
    static function setHeaderHtml(string $charset = 'utf-8')
    {
        header("Content-Type: text/html; charset=$charset");
    }

    /**
     * Set response header to XML
     *
     * @param string $charset
     */
    static function setHeaderXml(string $charset = 'utf-8')
    {
        header("Content-Type: text/xml; charset=$charset");
    }

    /**
     * Set response header to plain text
     *
     * @param string $charset
     */
    static function setHeaderText(string $charset = 'utf-8')
    {
        header("Content-Type: text/plain; charset=$charset");
    }

    /**
     * Set response header to JavaScript
     *
     * @param string $charset
     */
    static function setHeaderJs(string $charset = 'utf-8')
    {
        header("Content-Type: text/javascript; charset=$charset");
    }

    /**
     * Set response header to CSS
     *
     * @param string $charset
     */
    static function setHeaderCss(string $charset = 'utf-8')
    {
        header("Content-Type: text/css; charset=$charset");
    }

    /**
     * Set response header to PNG image
     */
    static function setHeaderPng(): void
    {
        header("Content-Type: image/png;");
    }

    /**
     * Set response header to WebP image
     */
    static function setHeaderWebp(): void
    {
        header("Content-Type: image/webp;");
    }

    /**
     * Set cache control headers
     *
     * @param int $ttl
     */
    static function setHeaderCache(int $ttl = 0): void
    {
        $ttl === 0 ?
            header("Cache-Control: no-cache") :
            header("Cache-Control: max-age=$ttl");
    }

    /**
     * Set Content Security Policy headers
     *
     * @param array $custom Custom directives (e.g. ['img-src' => 'https://example.com'])
     * @param bool $strict Enable strict mode
     */
    static function setHeaderCsp(bool $strict = false, array $custom = []): void
    {
        $policy = [
            'default-src' => ["'self'"],
            'script-src' => ["'self'"],
            'style-src' => ["'self'", "'unsafe-inline'"],
            'img-src' => ["'self'", "data:"],
            'font-src' => ["'self'"],
            'connect-src' => ["'self'"],
        ];

        if ($strict) {
            $policy['require-trusted-types-for'] = ["'script'"];
        }

        foreach ($custom as $type => $sources) {
            if (!isset($policy[$type])) {
                $policy[$type] = [];
            }
            $sources = is_array($sources) ? $sources : [$sources];
            $policy[$type] = array_merge($policy[$type], $sources);
        }

        $parts = [];
        foreach ($policy as $type => $sources) {
            $parts[] = "$type " . implode(' ', array_unique($sources));
        }

        header("Content-Security-Policy: " . implode('; ', $parts));
    }

    /**
     * Set HSTS security header
     */
    static function setHeaderHsts(int $ttl = 31536000): void
    {
        header("Strict-Transport-Security: max-age=$ttl; includeSubDomains; preload");
    }

    /**
     * Set Cross-Origin-Opener-Policy header
     */
    static function setHeaderCoop(): void
    {
        header('Cross-Origin-Opener-Policy: same-origin');
    }

    /**
     * Prevent page from being embedded in iframes
     */
    static function setHeaderXfo(): void
    {
        header('X-Frame-Options: DENY');
    }

    /**
     * Prevent indexing by robots
     */
    static function noRobots(): void
    {
        header('X-Robots-Tag: noindex, nofollow');
    }

    /**
     * Set 404 Not Found response
     */
    static function set404(): void
    {
        header('HTTP/1.1 404 Not Found', true, 404);
    }

    /**
     * Set 403 Forbidden response
     */
    static function set403(): void
    {
        header('HTTP/1.1 403 Forbidden', true, 403);
    }

    /**
     * Set 401 Unauthorized response
     */
    static function set401(): void
    {
        header('HTTP/1.1 401 Unauthorized', true, 401);
    }

    /**
     * Set 400 Bad Request response
     */
    static function set400(): void
    {
        header('HTTP/1.1 400 Bad Request', true, 400);
    }

    /**
     * Set 200 OK response
     */
    static function set200(): void
    {
        header('HTTP/1.1 200 OK', true, 200);
    }

    /**
     * Set HTTP response code
     *
     * @param int $code
     */
    static function setResponseCode(int $code): void
    {
        http_response_code($code);
    }

    /**
     * Get current HTTP response code
     *
     * @return int
     */
    static function getResponseCode(): int
    {
        return http_response_code() ?: 200;
    }

    /**
     * Redirect to a URL
     *
     * @param string $url
     * @param bool $is301
     */
    static function redirect(string $url, bool $is301 = false): void
    {
        $url = self::cleanPath($url);
        if ($is301) {
            header('HTTP/1.1 301 Moved Permanently');
        }
        header("Location: $url");
        exit;
    }

    /**
     * Reload the current page
     */
    static function refresh(): void
    {
        header("Refresh:0");
    }

    /**
     * Force HTTPS redirection
     */
    static function forceHttps(): void
    {
        if (
            (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') &&
            @fsockopen($_SERVER['HTTP_HOST'], 443, $e, $s, 1)
        ) {
            $redir = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            header("Location: $redir");
            exit;
        }
    }

    /**
     * Force download of a file
     *
     * @param string $path
     * @param string $mime
     */
    static function downloadFile(string $path, string $mime): void
    {
        if (!file_exists($path))
            return;
        $filename = basename($path);
        $size = filesize($path);
        header("Content-Type: $mime");
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Length: $size");
        readfile($path);
        exit;
    }

    /**
     * Clear browser cache
     */
    static function clearBrowserCache(): void
    {
        header('Pragma: no-cache');
        header('Cache: no-cache');
        header('Expires: Mon, 01 Jan 1970 00:00:00 GMT');
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
    }

    /**
     * Get base URI
     *
     * @return string
     */
    static function getBaseUri(): string
    {
        $scheme = (isset($_SERVER['HTTPS']) &&
            $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
        return $scheme . '://' . $_SERVER['HTTP_HOST'];
    }

    /**
     * Get URI path
     *
     * @return string
     */
    static function getPathUri(): string
    {
        $url = $_SERVER['REQUEST_URI'];
        $parsedUrl = parse_url($url);
        if (!is_array($parsedUrl) || !isset($parsedUrl['path']))
            return '/';
        $cleanPath = self::cleanPath($parsedUrl['path']);
        $cleanPath = rtrim($cleanPath, '/');
        return $cleanPath === '' ? '/' : $cleanPath;
    }

    /**
     * Get the previous path segment link
     *
     * @return string
     */
    static function getPathBack(): string
    {
        $path = self::getPathUri();
        if ($path === '/')
            return '';
        $segments = explode('/', trim($path, '/'));
        array_pop($segments);
        return $segments ? '/' . implode('/', $segments) : '/';
    }

    /**
     * Get URI
     *
     * @return string
     */
    static function getUri(): string
    {
        return self::getBaseUri() . self::getPathUri();
    }

    /**
     * Get browser language
     *
     * @param string $df
     * @return string
     */
    static function getBrowserLang(string $df = 'en'): string
    {
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $al = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
            return substr($al, 0, 2);
        }
        return substr($df, 0, 2);
    }

    /**
     * Get HTTP method
     *
     * @return string
     */
    static function getHttpMethod(): string
    {
        return $_SERVER['REQUEST_METHOD'] ?? 'GET';
    }

    /**
     * Get raw request body
     *
     * @return string
     */
    static function getHttpBody(bool $decode = false): string
    {
        $out = file_get_contents('php://input');
        $decode && $out = json_decode($out);
        return $out;
    }

    /**
     * Get GET, POST, COOKIE variables
     *
     * @return array
     */
    static function getHttpVars(): array
    {
        $out = [];
        foreach ($_POST as $k => $v) {
            $out[$k] = $v;
        }
        foreach ($_GET as $k => $v) {
            if (!array_key_exists($k, $out)) {
                $out[$k] = $v;
            }
        }
        foreach ($_COOKIE as $k => $v) {
            if (!array_key_exists($k, $out)) {
                $out[$k] = $v;
            }
        }
        return $out;
    }

    /**
     * Get merged HTTP parameters from JSON body and GET/POST
     *
     * @return array
     */
    static function getHttpParams(): array
    {
        $body = json_decode(self::getHttpBody(), true);
        $body = is_array($body) ? $body : [];
        $vars = self::getHttpVars();
        return array_merge($body, $vars);
    }

    /**
     * Get a single HTTP var by key
     *
     * @param string $k
     * @param mixed $default
     * @return mixed
     */
    static function getHttpVar(string $k = '', $default = '')
    {
        if ($k === '')
            return $default;
        if (isset($_GET[$k]))
            return $_GET[$k];
        if (isset($_POST[$k]))
            return $_POST[$k];
        if (isset($_COOKIE[$k]))
            return $_COOKIE[$k];
        return $default;
    }

    /**
     * Check if a single HTTP var exists
     *
     * @param string $k
     * @return bool
     */
    static function issetHttpVar(string $k = ''): bool
    {
        if ($k === '')
            return false;
        if (isset($_POST[$k]) || isset($_GET[$k]) || isset($_COOKIE[$k]))
            return true;
        return false;
    }

    /**
     * Get bearer token from Authorization header
     *
     * @return string
     */
    static function getBearerToken(): string
    {
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? null;
        if (!$header && function_exists('getallheaders')) {
            $headers = getallheaders();
            $header = $headers['Authorization'] ?? '';
        }
        if (stripos($header, 'Bearer ') === 0) {
            return trim(substr($header, 7));
        }
        return '';
    }

    /**
     * Get query string from $_GET, excluding 'q' and allowing overrides.
     * Returns only the raw query (no leading '?'), HTML-escaped.
     *
     * @param array $override
     * @return string
     */
    static function getQueryString(array $override = []): string
    {
        $params = $_GET;
        unset($params['q']);
        foreach ($override as $key => $value) {
            $params[$key] = $value;
        }
        $query = http_build_query($params);
        return htmlspecialchars($query, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Clean redundant slashes in a path or URL, preserving protocol double slashes
     *
     * @param string $path
     * @return string
     */
    static function cleanPath(string $path): string
    {
        return preg_replace('#(?<!:)//+#', '/', $path);
    }

    /**
     * Detect if request is from a mobile browser
     *
     * @return bool
     */
    static function isMobile(): bool
    {
        if (!isset($_SERVER['HTTP_USER_AGENT'])) {
            return false;
        }
        $ua = strtolower($_SERVER['HTTP_USER_AGENT']);
        $mobiles = [
            'android',
            'iphone',
            'ipad',
            'ipod',
            'blackberry',
            'opera mini',
            'windows phone',
            'mobile'
        ];
        foreach ($mobiles as $device) {
            if (strpos($ua, $device) !== false) {
                return true;
            }
        }
        return false;
    }

    static function isStandardBrowser(): bool
    {
        if (!isset($_SERVER['HTTP_USER_AGENT']))
            return false;
        $ua = strtolower($_SERVER['HTTP_USER_AGENT']);
        $standard = [
            'chrome',
            'firefox',
            'safari',
            'edge',
            'opera',
            'msie',
            'trident'
        ];
        foreach ($standard as $agent) {
            if (strpos($ua, $agent) !== false) {
                return true;
            }
        }
        return false;
    }

}
