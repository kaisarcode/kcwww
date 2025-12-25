<?php
/**
 * Controller - Base controller class
 * Summary: Abstract base for all controllers providing shared utilities.
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
 * Abstract base controller class.
 */
abstract class Controller {
    /**
     * Development mode flag.
     *
     * @var boolean
     */
    protected static bool $dev = false;

    /**
     * Initialize controller context.
     *
     * @return void
     */
    public static function init(): void {
        self::$dev = (bool) Conf::get('app.dev', false);
    }

    /**
     * Check if in development mode.
     *
     * @return boolean
     */
    protected static function isDev(): bool {
        return self::$dev;
    }

    /**
     * Return JSON encoded response.
     *
     * @param mixed $data Data to encode.
     *
     * @return string JSON string.
     */
    protected static function json(mixed $data): string {
        Http::setHeaderJson();
        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Render HTML template with data.
     *
     * @param string $template Path to template file.
     * @param array  $data     Additional data to merge.
     *
     * @return string Rendered HTML.
     */
    protected static function html(string $template, array $data = []): string {
        Http::setHeaderHtml();
        $cfg = Conf::get('tpl.conf', []);
        $tpl = new Template($cfg);
        $merged = array_merge(Conf::all(), $data);
        $out = $tpl->parse($template, $merged);
        return self::minify($out);
    }

    /**
     * Minify content based on dev mode.
     *
     * @param string $content Content to minify.
     *
     * @return string Minified content.
     */
    protected static function minify(string $content): string {
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
     * Block robots from indexing.
     *
     * @return void
     */
    protected static function noRobots(): void {
        Http::noRobots();
    }

    /**
     * Get request parameter from GET, POST or JSON body.
     *
     * @param string $key     Parameter key.
     * @param mixed  $default Default value if not found.
     *
     * @return mixed Parameter value.
     */
    protected static function param(string $key, mixed $default = null) {
        return Http::getHttpVar($key, $default);
    }

    /**
     * Get all request parameters.
     *
     * @return array All parameters.
     */
    protected static function params(): array {
        return Http::getHttpParams();
    }

    /**
     * Set HTTP status code.
     *
     * @param integer $code HTTP status code.
     *
     * @return void
     */
    protected static function status(int $code): void {
        Http::setResponseCode($code);
    }

    /**
     * Redirect to URL.
     *
     * @param string  $url       Destination URL.
     * @param boolean $permanent Use 301 instead of 302.
     *
     * @return void
     */
    protected static function redirect(string $url, bool $permanent = false): void {
        Http::redirect($url, $permanent);
    }
}
