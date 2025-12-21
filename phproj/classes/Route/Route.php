<?php
/**
 * Route - Minimalistic HTTP router
 * Summary: Matches request method and URI path, automatically halts on first matching route
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
 * Minimalistic HTTP router that matches request method and URI path.
 * Automatically halts on first matching route unless the callback returns false.
 *
 * Usage:
 * Route::get('/path', function() { return 'Hello'; });
 * Route::all('/.*', function() { return false; });
 *
 * Returned values from route callbacks:
 * - false -> continue to next route
 * - true -> stop, no output
 * - string or number -> echoed as response
 * - array or object -> JSON-encoded and echoed
 */
class Route
{
    public static $matched = false;
    private static $errorHandler = null;
    private static $errorRegistered = false;
    private static $inputCache = null;
    private static $authParam = 'routepassword';

    /**
     * Registers a route for a specific HTTP method.
     *
     * @param string $mth HTTP method (GET, POST, etc.)
     * @param string $pth URI pattern, simple or regex-like
     * @param callable $cb Callback to execute if matched
     * @return void
     */
    public static function add($mth, $pth, $cb)
    {
        if (self::$matched)
            return;
        $mth = strtoupper(trim($mth));
        $mthd = $_SERVER['REQUEST_METHOD'];
        $rawUri = $_SERVER['REQUEST_URI'];
        $parsed = parse_url($rawUri);
        $rawPath = $parsed['path'] ?? '/';
        $normalizedPath = preg_replace('#/+#', '/', $rawPath);
        $normalizedPath = rtrim($normalizedPath, '/');
        $normalizedPath = $normalizedPath === '' ? '/' : $normalizedPath;
        if ($rawPath !== $normalizedPath) {
            $normalizedUri = $normalizedPath;
            if (isset($parsed['query'])) {
                $normalizedUri .= '?' . $parsed['query'];
            }
            header("Location: $normalizedUri", true, 301);
            exit;
        }
        if ($mth == $mthd || $mth == 'ALL') {
            $patt = str_replace('/', '\\/', $pth);
            if (preg_match("/^$patt$/", $normalizedPath, $mtch)) {
                $res = $cb($mtch);
                if ($res === false)
                    return;
                if (is_string($res) || is_numeric($res)) {
                    echo $res;
                } elseif (is_array($res) || is_object($res)) {
                    echo json_encode($res);
                }
                self::$matched = true;
            }
        }
    }

    /**
     * Registers a route for any HTTP method.
     *
     * @param string $pth
     * @param callable $cb
     * @return void
     */
    public static function all($pth, $cb)
    {
        self::add('all', $pth, $cb);
    }

    /**
     * Registers a GET route.
     *
     * @param string $pth
     * @param callable $cb
     * @return void
     */
    public static function get($pth, $cb)
    {
        self::add('get', $pth, $cb);
    }

    /**
     * Registers a POST route.
     *
     * @param string $pth
     * @param callable $cb
     * @return void
     */
    public static function post($pth, $cb)
    {
        self::add('post', $pth, $cb);
    }

    /**
     * Registers a PUT route.
     *
     * @param string $pth
     * @param callable $cb
     * @return void
     */
    public static function put($pth, $cb)
    {
        self::add('put', $pth, $cb);
    }

    /**
     * Registers a DELETE route.
     *
     * @param string $pth
     * @param callable $cb
     * @return void
     */
    public static function delete($pth, $cb)
    {
        self::add('delete', $pth, $cb);
    }

    /**
     * Registers automatic error callback executed if no route matches.
     *
     * @param callable $cb
     * @return void
     */
    public static function error(callable $cb): void
    {
        self::$errorHandler = $cb;
        if (self::$errorRegistered)
            return;
        self::$errorRegistered = true;
        register_shutdown_function(function () {
            $handler = Route::$errorHandler;
            if (!$handler)
                return;
            if (headers_sent())
                return;
            $code = http_response_code();
            if (!Route::$matched) {
                $code = 404;
            }
            if ($code < 400) {
                return;
            }
            $code = $code ?: 500;
            http_response_code($code);
            $handler($code);
        });
    }

    /**
     * Protects a route with a simple password check. If the password matches,
     * routing continues to the next matching route; otherwise responds 401.
     *
     * @param string $mth HTTP method
     * @param string $pth URI pattern
     * @param string $pwd Required password
     * @return void
     */
    public static function protect($mth, $pth, $pwd)
    {
        self::add($mth, $pth, function ($mtch) use ($pwd) {
            $provided = self::readPassword();
            if ($provided !== $pwd) {
                header('HTTP/1.1 401 Unauthorized');
                http_response_code(401);
                return true;
            }
            return false;
        });
    }

    /**
     * Sets the parameter name used for authentication.
     *
     * @param string $name
     * @return void
     */
    public static function setAuthParam(string $name)
    {
        self::$authParam = $name;
    }

    /**
     * Extracts password provided by the user from query, form, or raw payload.
     *
     * @return string|null
     */
    private static function readPassword(): ?string
    {
        $p = self::$authParam;
        if (isset($_REQUEST[$p]))
            return (string) $_REQUEST[$p];
        if (isset($_COOKIE[$p]))
            return (string) $_COOKIE[$p];

        $raw = self::readRawInput();
        if ($raw !== '') {
            parse_str($raw, $parsed);
            if (isset($parsed[$p]))
                return (string) $parsed[$p];

            $json = json_decode($raw, true);
            if (is_array($json)) {
                if (isset($json[$p]))
                    return (string) $json[$p];
            }
        }

        return null;
    }

    /**
     * Reads raw request body once and caches it.
     *
     * @return string
     */
    private static function readRawInput(): string
    {
        if (self::$inputCache !== null) {
            return self::$inputCache;
        }
        $body = file_get_contents('php://input');
        self::$inputCache = $body === false ? '' : $body;
        return self::$inputCache;
    }
}
