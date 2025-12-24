<?php
/**
 * Controller - Base controller class
 * Summary: Abstract base for all controllers providing shared utilities
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
 * Abstract base controller class
 */
abstract class Controller
{
    /**
     * Development mode flag
     */
    protected static bool $dev = false;

    /**
     * Initialize controller context
     */
    public static function init(): void
    {
        self::$dev = (bool) Conf::get('app.dev', false);
    }

    /**
     * Check if in development mode
     */
    protected static function isDev(): bool
    {
        return self::$dev;
    }

    /**
     * Return JSON encoded response
     *
     * @param mixed $data
     * @return string
     */
    protected static function json($data): string
    {
        Http::setHeaderJson();
        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Render HTML template with data
     *
     * @param string $template Path to template file
     * @param array $data Additional data to merge
     * @return string
     */
    protected static function html(string $template, array $data = []): string
    {
        Http::setHeaderHtml();
        $cfg = Conf::get('tpl.conf', []);
        $tpl = new Template($cfg);
        $merged = array_merge(Conf::all(), $data);
        $out = $tpl->parse($template, $merged);
        return self::minify($out);
    }

    /**
     * Minify content based on dev mode
     *
     * @param string $content
     * @return string
     */
    protected static function minify(string $content): string
    {
        if (self::$dev) {
            return trim($content);
        }
        $content = Str::keepPre($content);
        $content = Str::rmc($content);
        $content = Str::min($content);
        $content = Str::restorePre($content);
        return trim($content);
    }

    /**
     * Block robots from indexing
     */
    protected static function noRobots(): void
    {
        Http::noRobots();
    }

    /**
     * Get request parameter from GET, POST or JSON body
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    protected static function param(string $key, $default = null)
    {
        return Http::getHttpVar($key, $default);
    }

    /**
     * Get all request parameters
     *
     * @return array
     */
    protected static function params(): array
    {
        return Http::getHttpParams();
    }

    /**
     * Set HTTP status code
     *
     * @param int $code
     */
    protected static function status(int $code): void
    {
        Http::setResponseCode($code);
    }

    /**
     * Redirect to URL
     *
     * @param string $url
     * @param bool $permanent
     */
    protected static function redirect(string $url, bool $permanent = false): void
    {
        Http::redirect($url, $permanent);
    }
}
