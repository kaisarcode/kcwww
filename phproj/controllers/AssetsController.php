<?php
/**
 * AssetsController - Asset bundling controller
 * Summary: Serves bundled CSS and JavaScript files
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
 * Asset bundling controller
 */
class AssetsController extends Controller
{
    /**
     * Serve bundled CSS
     *
     * @return string
     */
    public static function styles(): string
    {
        self::noRobots();
        Http::setHeaderCss();

        $file = Conf::get('assets.css.entry');
        if (!$file || !file_exists($file)) {
            self::status(404);
            return '';
        }

        $out = Bundler::css($file);
        return self::minify($out);
    }

    /**
     * Serve bundled JavaScript
     *
     * @return string
     */
    public static function script(): string
    {
        self::noRobots();
        Http::setHeaderJs();

        $file = Conf::get('assets.js.entry');
        if (!$file || !file_exists($file)) {
            self::status(404);
            return '';
        }

        $out = Bundler::js($file);
        return self::minify($out);
    }
}
