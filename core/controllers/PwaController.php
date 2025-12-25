<?php
/**
 * PwaController - Progressive Web App controller
 * Summary: Serves PWA manifest, service worker, and app icons
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
 * PWA controller
 */
class PwaController extends Controller {
    /**
     * Serve manifest.json
     *
     * @return string
     */
    public static function manifest(): string {
        self::noRobots();

        $manifest = [
            'lang' => Conf::get('app.lang', 'en'),
            'name' => Conf::get('app.name', 'App'),
            'short_name' => Conf::get('app.name', 'App'),
            'description' => Conf::get('app.desc', ''),
            'orientation' => 'any',
            'scope' => '.',
            'id' => '/',
            'start_url' => '/',
            'display' => 'standalone',
            'theme_color' => Conf::get('app.color', '#ffffff'),
            'background_color' => Conf::get('app.color', '#ffffff'),
            'icons' => self::buildIcons(),
            'screenshots' => self::buildScreenshots(),
        ];

        return self::json($manifest);
    }

    /**
     * Serve service worker
     *
     * @return string
     */
    public static function worker(): string {
        self::noRobots();
        Http::setHeaderJs();

        $file = Conf::get('assets.worker.entry');
        if (!$file || !file_exists($file)) {
            self::status(404);
            return '';
        }

        // Parse as template to allow config injection
        $cfg = Conf::get('tpl.conf', []);
        $tpl = new Template($cfg);
        $out = $tpl->parse($file, Conf::all());

        return self::minify($out);
    }

    /**
     * Serve app icon at specified size
     *
     * @param integer $size
     * @return string
     */
    public static function icon(int $size): string {
        Http::setHeaderPng();

        $srcDir = Conf::get('app.image.src_dir');
        $iconSrc = Conf::get('app.icon.src', $srcDir . '/app.svg');

        if (!file_exists($iconSrc)) {
            self::status(404);
            return '';
        }

        return self::getCachedIcon($iconSrc, $size);
    }

    /**
     * Serve favicon at specified size
     *
     * @param integer $size
     * @return string
     */
    public static function favicon(int $size): string {
        Http::setHeaderPng();

        $srcDir = Conf::get('app.image.src_dir');
        $iconSrc = Conf::get('app.favicon.src', $srcDir . '/ico.svg');

        if (!file_exists($iconSrc)) {
            self::status(404);
            return '';
        }

        return self::getCachedIcon($iconSrc, $size);
    }

    /**
     * Build icons array for manifest
     *
     * @return array
     */
    private static function buildIcons(): array {
        $icons = [];
        $sizes = Conf::get('app.icon.sizes', [192, 512]);

        foreach ($sizes as $size) {
            foreach (['any', 'maskable'] as $purpose) {
                $icons[] = [
                    'src' => "/appicon-{$size}.png",
                    'sizes' => "{$size}x{$size}",
                    'type' => 'image/png',
                    'purpose' => $purpose,
                ];
            }
        }

        return $icons;
    }

    /**
     * Build screenshots array for manifest
     *
     * @return array
     */
    private static function buildScreenshots(): array {
        $screenshots = Conf::get('app.screenshots', []);
        if (empty($screenshots)) {
            return [];
        }

        $result = [];
        foreach ($screenshots as $ss) {
            $result[] = [
                'src' => $ss['src'] ?? '',
                'sizes' => $ss['sizes'] ?? '',
                'type' => $ss['type'] ?? 'image/png',
                'form_factor' => $ss['form_factor'] ?? 'wide',
            ];
        }

        return $result;
    }

    /**
     * Get cached icon or generate and cache
     *
     * @param string  $src
     * @param integer $size
     * @return string
     */
    private static function getCachedIcon(string $src, int $size): string {
        $cacheDir = Conf::get('app.image.cache_dir', sys_get_temp_dir());
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0775, true);
        }

        $cacheKey = md5($src . $size . 'png');
        $cacheFile = "$cacheDir/$cacheKey.png";

        if (file_exists($cacheFile)) {
            $srcMtime = filemtime($src);
            if (filemtime($cacheFile) >= $srcMtime) {
                return file_get_contents($cacheFile);
            }
        }

        $out = Img::proc($src, $size, $size, 'png');
        if ($out !== '') {
            file_put_contents($cacheFile, $out);
        }

        return $out;
    }
}
