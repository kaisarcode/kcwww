<?php
/**
 * ImagesController - Dynamic image processing
 * Summary: Handles image resizing, format conversion, and caching
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
 * Dynamic image processing controller
 */
class ImagesController extends Controller
{
    /**
     * Process and serve dynamic image
     *
     * Pattern: /img/{path/to/name}-{dims}.{format}
     * Dims: w100, h200, w100h200
     *
     * @param array $matches Regex matches from route
     * @return string Image binary data
     */
    public static function process(array $matches): string
    {
        if (!isset($matches[3])) {
            self::status(404);
            return '';
        }

        $name = $matches[1];
        $dimStr = $matches[2];
        $ext = strtolower($matches[3]);

        // Validate source file
        $srcDir = Conf::get('app.image.src_dir');
        $allowedBase = realpath($srcDir);

        if ($allowedBase === false) {
            self::status(404);
            return '';
        }

        $srcFile = self::findSourceFile($srcDir, $allowedBase, $name);
        if ($srcFile === '') {
            self::status(404);
            return '';
        }

        // Parse dimensions
        list($w, $h) = self::parseDimensions($dimStr);
        if (!self::validateDimensions($w, $h)) {
            self::status(404);
            return '';
        }

        // Determine output format
        $defaultExt = Conf::get('app.image.default_extension', 'webp');
        $outputExt = $ext ?: $defaultExt;

        // Get or generate cached image
        $out = self::getCachedImage($srcFile, $w, $h, $outputExt, $dimStr);

        // Set content type
        self::setImageHeader($outputExt);

        return $out;
    }

    /**
     * Find source file with any allowed extension
     */
    private static function findSourceFile(string $srcDir, string $allowedBase, string $name): string
    {
        $searchExts = Conf::get('app.image.allowed_extensions', ['png', 'jpg', 'jpeg', 'webp', 'svg']);

        foreach ($searchExts as $search) {
            $candidate = "$srcDir/$name.$search";
            $realPath = realpath($candidate);

            // Security: Verify path is within allowed base (prevent traversal)
            if ($realPath && strpos($realPath, $allowedBase) === 0) {
                return $realPath;
            }
        }

        return '';
    }

    /**
     * Parse dimension string into width and height
     */
    private static function parseDimensions(string $dimStr): array
    {
        $w = 0;
        $h = 0;

        if (preg_match('/w(\d+)/', $dimStr, $wm)) {
            $w = (int) $wm[1];
        }
        if (preg_match('/h(\d+)/', $dimStr, $hm)) {
            $h = (int) $hm[1];
        }

        return [$w, $h];
    }

    /**
     * Validate dimensions against allowed sizes
     */
    private static function validateDimensions(int $w, int $h): bool
    {
        $allowedSizes = Conf::get('app.image.allowed_sizes', [50, 100, 200, 300, 400, 500, 800, 1200]);

        if ($w > 0 && !in_array($w, $allowedSizes)) {
            return false;
        }
        if ($h > 0 && !in_array($h, $allowedSizes)) {
            return false;
        }

        return true;
    }

    /**
     * Get cached image or generate and cache
     */
    private static function getCachedImage(string $srcFile, int $w, int $h, string $ext, string $dimStr): string
    {
        $cacheDir = Conf::get('app.image.cache_dir', sys_get_temp_dir());
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0775, true);
        }

        $cacheKey = md5($srcFile . $dimStr . $ext);
        $cacheFile = "$cacheDir/$cacheKey.$ext";
        $srcMtime = filemtime($srcFile);

        // Return cached if valid
        if (file_exists($cacheFile) && filemtime($cacheFile) >= $srcMtime) {
            return file_get_contents($cacheFile);
        }

        // Calculate missing dimension
        list($w, $h) = self::calculateMissingDimension($srcFile, $w, $h);

        // Generate image
        $out = Img::proc($srcFile, $w, $h, $ext);

        // Cache result
        if ($out !== '') {
            file_put_contents($cacheFile, $out);
        }

        return $out;
    }

    /**
     * Calculate missing dimension maintaining aspect ratio
     */
    private static function calculateMissingDimension(string $srcFile, int $w, int $h): array
    {
        if (($w === 0 || $h === 0) && !($w === 0 && $h === 0)) {
            $sizes = @getimagesize($srcFile);
            if ($sizes) {
                list($srcW, $srcH) = $sizes;

                if ($w === 0 && $srcW > 0 && $srcH > 0) {
                    $w = (int) round(($h / $srcH) * $srcW);
                } elseif ($h === 0 && $srcH > 0 && $srcW > 0) {
                    $h = (int) round(($w / $srcW) * $srcH);
                }
            }
        }

        // Default if still zero
        if ($w === 0 && $h > 0) {
            $w = $h;
        } elseif ($h === 0 && $w > 0) {
            $h = $w;
        }

        return [$w, $h];
    }

    /**
     * Set appropriate Content-Type header for image
     */
    private static function setImageHeader(string $ext): void
    {
        $mimes = [
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'webp' => 'image/webp',
        ];

        $mime = $mimes[$ext] ?? 'application/octet-stream';
        header("Content-Type: $mime");
    }
}
