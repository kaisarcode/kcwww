<?php
/**
 * Http - HTTP utilities
 * Summary: Provides utilities for HTTP operations and response handling.
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
 * HTTP utilities.
 */
class Http {
    /**
     * Allow cross-origin requests.
     *
     * @param string $origin Origin to allow.
     *
     * @return void
     */
    public static function allowOrigin(string $origin = '*'): void {
        header("Access-Control-Allow-Origin: $origin");
        header('Access-Control-Allow-Headers: Content-Type');
    }

    /**
     * Set response header to JSON.
     *
     * @param string $charset Character set.
     *
     * @return void
     */
    public static function setHeaderJson(string $charset = 'utf-8'): void {
        header("Content-Type: application/json; charset=$charset");
    }

    /**
     * Set response header to HTML.
     *
     * @param string $charset Character set.
     *
     * @return void
     */
    public static function setHeaderHtml(string $charset = 'utf-8'): void {
        header("Content-Type: text/html; charset=$charset");
    }

    /**
     * Set response header to XML.
     *
     * @param string $charset Character set.
     *
     * @return void
     */
    public static function setHeaderXml(string $charset = 'utf-8'): void {
        header("Content-Type: text/xml; charset=$charset");
    }

    /**
     * Set response header to plain text.
     *
     * @param string $charset Character set.
     *
     * @return void
     */
    public static function setHeaderText(string $charset = 'utf-8'): void {
        header("Content-Type: text/plain; charset=$charset");
    }

    /**
     * Set response header to JavaScript.
     *
     * @param string $charset Character set.
     *
     * @return void
     */
    public static function setHeaderJs(string $charset = 'utf-8'): void {
        header("Content-Type: text/javascript; charset=$charset");
    }

    /**
     * Set response header to CSS.
     *
     * @param string $charset Character set.
     *
     * @return void
     */
    public static function setHeaderCss(string $charset = 'utf-8'): void {
        header("Content-Type: text/css; charset=$charset");
    }

    /**
     * Set response header to PNG image.
     *
     * @return void
     */
    public static function setHeaderPng(): void {
        header("Content-Type: image/png;");
    }

    /**
     * Set response header to WebP image.
     *
     * @return void
     */
    public static function setHeaderWebp(): void {
        header("Content-Type: image/webp;");
    }

    /**
     * Set cache control headers.
     *
     * @param integer $ttl Time to live in seconds.
     *
     * @return void
     */
    public static function setHeaderCache(int $ttl = 0): void {
        if ($ttl === 0) {
            header("Cache-Control: no-cache");
        } else {
            header("Cache-Control: max-age=$ttl");
        }
    }

    /**
     * Set Content Security Policy headers.
     *
     * @param boolean $strict Enable strict mode.
     * @param array   $custom Custom directives.
     *
     * @return void
     */
    public static function setHeaderCsp(bool $strict = false, array $custom = []): void {
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
     * Set HSTS security header.
     *
     * @param integer $ttl Time to live in seconds.
     *
     * @return void
     */
    public static function setHeaderHsts(int $ttl = 31536000): void {
        header("Strict-Transport-Security: max-age=$ttl; includeSubDomains; preload");
    }

    /**
     * Set Cross-Origin-Opener-Policy header.
     *
     * @return void
     */
    public static function setHeaderCoop(): void {
        header('Cross-Origin-Opener-Policy: same-origin');
    }

    /**
     * Prevent page from being embedded in iframes.
     *
     * @return void
     */
    public static function setHeaderXfo(): void {
        header('X-Frame-Options: DENY');
    }

    /**
     * Prevent indexing by robots.
     *
     * @return void
     */
    public static function noRobots(): void {
        header('X-Robots-Tag: noindex, nofollow');
    }

    /**
     * Set 404 Not Found response.
     *
     * @return void
     */
    public static function set404(): void {
        header('HTTP/1.1 404 Not Found', true, 404);
    }

    /**
     * Set 403 Forbidden response.
     *
     * @return void
     */
    public static function set403(): void {
        header('HTTP/1.1 403 Forbidden', true, 403);
    }

    /**
     * Set 401 Unauthorized response.
     *
     * @return void
     */
    public static function set401(): void {
        header('HTTP/1.1 401 Unauthorized', true, 401);
    }

    /**
     * Set 400 Bad Request response.
     *
     * @return void
     */
    public static function set400(): void {
        header('HTTP/1.1 400 Bad Request', true, 400);
    }

    /**
     * Set 200 OK response.
     *
     * @return void
     */
    public static function set200(): void {
        header('HTTP/1.1 200 OK', true, 200);
    }

    /**
     * Set HTTP response code.
     *
     * @param integer $code HTTP status code.
     *
     * @return void
     */
    public static function setResponseCode(int $code): void {
        http_response_code($code);
    }

    /**
     * Get current HTTP response code.
     *
     * @return integer Response code.
     */
    public static function getResponseCode(): int {
        return http_response_code() ?: 200;
    }

    /**
     * Redirect to a URL.
     *
     * @param string  $url   Destination URL.
     * @param boolean $is301 Use permanent redirect.
     *
     * @return void
     */
    public static function redirect(string $url, bool $is301 = false): void {
        $url = self::cleanPath($url);
        if ($is301) {
            header('HTTP/1.1 301 Moved Permanently');
        }
        header("Location: $url");
        exit;
    }

    /**
     * Reload the current page.
     *
     * @return void
     */
    public static function refresh(): void {
        header("Refresh:0");
    }

    /**
     * Force HTTPS redirection.
     *
     * @return void
     */
    public static function forceHttps(): void {
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
     * Force download of a file.
     *
     * @param string $path Path to file.
     * @param string $mime MIME type.
     *
     * @return void
     */
    public static function downloadFile(string $path, string $mime): void {
        if (!file_exists($path)) {
            return;
        }
        $filename = basename($path);
        $size = filesize($path);
        header("Content-Type: $mime");
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Length: $size");
        readfile($path);
        exit;
    }

    /**
     * Clear browser cache.
     *
     * @return void
     */
    public static function clearBrowserCache(): void {
        header('Pragma: no-cache');
        header('Cache: no-cache');
        header('Expires: Mon, 01 Jan 1970 00:00:00 GMT');
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
    }

    /**
     * Get base URI.
     *
     * @return string Base URI with protocol.
     */
    public static function getBaseUri(): string {
        $scheme = 'http';
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            $scheme = 'https';
        }
        return $scheme . '://' . $_SERVER['HTTP_HOST'];
    }

    /**
     * Get URI path.
     *
     * @return string Path component.
     */
    public static function getPathUri(): string {
        $url = $_SERVER['REQUEST_URI'] ?? '/';
        $parsedUrl = parse_url($url);
        if (!is_array($parsedUrl) || !isset($parsedUrl['path'])) {
            return '/';
        }
        $cleanPath = self::cleanPath($parsedUrl['path']);
        $cleanPath = rtrim($cleanPath, '/');
        return $cleanPath === '' ? '/' : $cleanPath;
    }

    /**
     * Get the previous path segment link.
     *
     * @return string Parent path.
     */
    public static function getPathBack(): string {
        $path = self::getPathUri();
        if ($path === '/') {
            return '';
        }
        $segments = explode('/', trim($path, '/'));
        array_pop($segments);
        return $segments ? '/' . implode('/', $segments) : '/';
    }

    /**
     * Get URI.
     *
     * @return string Full URI.
     */
    public static function getUri(): string {
        return self::getBaseUri() . self::getPathUri();
    }

    /**
     * Get browser language.
     *
     * @param string $df Default language.
     *
     * @return string Language code.
     */
    public static function getBrowserLang(string $df = 'en'): string {
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $al = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
            return substr($al, 0, 2);
        }
        return substr($df, 0, 2);
    }

    /**
     * Get HTTP method.
     *
     * @return string Request method.
     */
    public static function getHttpMethod(): string {
        return $_SERVER['REQUEST_METHOD'] ?? 'GET';
    }

    /**
     * Get raw request body.
     *
     * @param boolean $decode Decode as JSON.
     *
     * @return string Request body.
     */
    public static function getHttpBody(bool $decode = false): string {
        $out = file_get_contents('php://input');
        if ($decode) {
            $out = json_decode($out);
        }
        return $out;
    }

    /**
     * Get GET POST COOKIE variables.
     *
     * @return array Merged variables.
     */
    public static function getHttpVars(): array {
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
     * Get merged HTTP parameters from JSON body and GET POST.
     *
     * @return array Merged parameters.
     */
    public static function getHttpParams(): array {
        $body = json_decode(self::getHttpBody(), true);
        $body = is_array($body) ? $body : [];
        $vars = self::getHttpVars();
        return array_merge($body, $vars);
    }

    /**
     * Get a single HTTP var by key.
     *
     * @param string $k       Variable key.
     * @param mixed  $default Default value.
     *
     * @return mixed Variable value.
     */
    public static function getHttpVar(string $k = '', mixed $default = '') {
        if ($k === '') {
            return $default;
        }
        if (isset($_GET[$k])) {
            return $_GET[$k];
        }
        if (isset($_POST[$k])) {
            return $_POST[$k];
        }
        if (isset($_COOKIE[$k])) {
            return $_COOKIE[$k];
        }
        return $default;
    }

    /**
     * Check if a single HTTP var exists.
     *
     * @param string $k Variable key.
     *
     * @return boolean True if exists.
     */
    public static function issetHttpVar(string $k = ''): bool {
        if ($k === '') {
            return false;
        }
        if (isset($_POST[$k]) || isset($_GET[$k]) || isset($_COOKIE[$k])) {
            return true;
        }
        return false;
    }

    /**
     * Get bearer token from Authorization header.
     *
     * @return string Bearer token.
     */
    public static function getBearerToken(): string {
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
     * Get query string from GET excluding q and allowing overrides.
     *
     * @param array $override Override parameters.
     *
     * @return string Query string.
     */
    public static function getQueryString(array $override = []): string {
        $params = $_GET;
        unset($params['q']);
        foreach ($override as $key => $value) {
            $params[$key] = $value;
        }
        $query = http_build_query($params);
        return htmlspecialchars($query, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Clean redundant slashes in a path or URL.
     *
     * @param string $path Path to clean.
     *
     * @return string Cleaned path.
     */
    public static function cleanPath(string $path): string {
        return preg_replace('#(?<!:)//+#', '/', $path);
    }

    /**
     * Detect if request is from a mobile browser.
     *
     * @return boolean True if mobile.
     */
    public static function isMobile(): bool {
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

    /**
     * Detect if request is from a standard browser.
     *
     * @return boolean True if standard browser.
     */
    public static function isStandardBrowser(): bool {
        if (!isset($_SERVER['HTTP_USER_AGENT'])) {
            return false;
        }
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
