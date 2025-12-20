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

    /**
     * Serve a static file from views directory
     *
     * @param string $path
     * @return string
     */
    public static function file(string $path): string
    {
        self::noRobots();

        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $mimes = [
            'woff2' => 'font/woff2',
            'woff' => 'font/woff',
            'ttf' => 'font/ttf',
            'otf' => 'font/otf',
            'svg' => 'image/svg+xml',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'webp' => 'image/webp',
            'pdf' => 'application/pdf',
            'json' => 'application/json',
            'txt' => 'text/plain',
        ];

        if (!isset($mimes[$ext])) {
            self::status(404);
            return '';
        }

        $locations = [];
        if (defined('VIEWS_OVERRIDE')) {
            $locations[] = VIEWS_OVERRIDE;
        }
        $locations[] = VIEWS;

        $found = '';
        foreach ($locations as $loc) {
            $candidate = $loc . '/' . $path;
            $realCandidate = realpath($candidate);
            $realLoc = realpath($loc);

            if (!$realCandidate || !$realLoc) {
                continue;
            }

            // Security: File must be within base directory
            $realLoc = rtrim($realLoc, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
            if (is_file($realCandidate) && str_starts_with($realCandidate, $realLoc)) {
                $found = $realCandidate;
                break;
            }
        }

        if (!$found) {
            self::status(404);
            return '';
        }

        header("Content-Type: " . $mimes[$ext]);
        header('Cache-Control: max-age=31536000, public');
        return file_get_contents($found);
    }
}
