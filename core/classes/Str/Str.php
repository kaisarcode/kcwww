<?php
/**
 * Str - String manipulation utilities
 * Summary: Provides utility functions for string manipulation including sanitization, truncation, and slug generation
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
 * Provides utility functions for string manipulation.
 */
class Str {
    /**
     * Minify string by collapsing whitespace.
     *
     * @param string $str
     * @return string
     */
    public static function min(string $str = ''): string {
        return preg_replace('/\s+/', ' ', $str);
    }

    /**
     * Minify CSS by removing unnecessary whitespace and characters.
     *
     * @param string $css CSS content to minify.
     *
     * @return string Minified CSS.
     */
    public static function minCss(string $css = ''): string {
        $css = preg_replace('/\/\*[\s\S]*?\*\//', '', $css);
        $css = preg_replace('/\s*([{}:;,>+~])\s*/', '$1', $css);
        $css = str_replace(';}', '}', $css);
        $css = preg_replace('/\s+/', ' ', $css);
        $css = preg_replace('/(:|\s)0\.(\d+)/', '${1}.${2}', $css);
        $css = preg_replace('/(:|\s)0(px|em|rem|%|pt|cm|mm|in|pc|ex|ch|vw|vh|vmin|vmax)/', '${1}0', $css);
        return trim($css);
    }

    /**
     * Minify JavaScript by removing comments and unnecessary whitespace.
     *
     * @param string $js JavaScript content to minify.
     *
     * @return string Minified JavaScript.
     */
    public static function minJs(string $js = ''): string {
        $js = preg_replace('/\/\*[\s\S]*?\*\//', '', $js);
        $js = preg_replace('/(?<!:)\/\/[^\n]*/', '', $js);
        $js = preg_replace('/\s*([{}();,:<>+\-*\/=!&|?])\s*/', '$1', $js);
        $js = preg_replace('/\s+/', ' ', $js);
        return trim($js);
    }

    /**
     * Remove HTML and JS comments from string.
     *
     * @param string $str
     * @return string
     */
    public static function rmc(string $str = ''): string {
        $str = preg_replace('/<!--[\s\S]*?-->/', '', $str);
        $str = preg_replace('/\/\*[\s\S]*?\*\/|(?<!:)\/\/.*/m', '', $str);
        return $str;
    }

    /**
     * Truncate string with optional ellipsis.
     *
     * @param string  $str
     * @param integer $len
     * @param string  $ellipsis
     * @return string
     */
    public static function truncate(string $str, int $len = 30, string $ellipsis = '...'): string {
        return strlen($str) > $len ? substr($str, 0, $len) . $ellipsis : $str;
    }

    /**
     * Sanitize string to prevent XSS.
     *
     * @param string $str
     * @return string
     */
    public static function sanitize(string $str = ''): string {
        return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Generate a secure random string.
     *
     * @param integer $length
     * @param string  $chars
     * @return string
     */
    public static function random(int $length, string $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'): string {
        $result = '';
        $max = strlen($chars) - 1;
        $bytes = random_bytes($length);
        for ($i = 0; $i < $length; $i++) {
            $result .= $chars[ord($bytes[$i]) % ($max + 1)];
        }
        return $result;
    }

    /**
     * Escape formatting inside <pre> blocks.
     *
     * @param string $str
     * @return string
     */
    public static function keepPre(string $str = ''): string {
        return preg_replace_callback('/<pre>(.*?)<\/pre>/is', function ($matches) {
            $escaped = str_replace(
                ["\n", " ", "<", ">"],
                ["__NL__", "__SP__", "__LT__", "__GT__"],
                $matches[1]
            );
            return '<pre>' . $escaped . '</pre>';
        }, $str);
    }

    /**
     * Restore formatting inside <pre> blocks.
     *
     * @param string $str
     * @return string
     */
    public static function restorePre(string $str = ''): string {
        return str_replace(
            ["__NL__", "__SP__", "__LT__", "__GT__"],
            ["\n", " ", "<", ">"],
            $str
        );
    }

    /**
     * Remove prefix from the beginning of a string, default is single space.
     *
     * @param string $str
     * @param string $c
     * @return string
     */
    public static function ltrim(string $str = '', string $c = ' '): string {
        $len = strlen($c);
        if ($len > 0 && substr($str, 0, $len) === $c) {
            return substr($str, $len);
        }
        return $str;
    }

    /**
     * Remove suffix from the end of a string, default is single space.
     *
     * @param string $str
     * @param string $c
     * @return string
     */
    public static function rtrim(string $str = '', string $c = ' '): string {
        $len = strlen($c);
        if ($len > 0 && substr($str, -$len) === $c) {
            return substr($str, 0, -$len);
        }
        return $str;
    }

    /**
     * Remove matching prefix and suffix from string. Default is single space.
     *
     * @param string $str
     * @param string $c
     * @return string
     */
    public static function trim(string $str = '', string $c = ' '): string {
        $str = self::ltrim($str, $c);
        $str = self::rtrim($str, $c);
        return $str;
    }

    /**
     * Normalize string by removing accents and diacritics.
     *
     * @param string $str
     * @return string
     */
    public static function normalize(string $str): string {
        return preg_replace('/[\x00-\x1F\x7F]/u', '', iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $str));
    }

    /**
     * Generate a URL-safe slug from a string.
     *
     * @param string $str
     * @return string
     */
    public static function slug(string $str): string {
        $str = self::normalize($str);
        $str = strtolower($str);
        $str = preg_replace('/[^a-z0-9_-]+/', '-', $str);
        return trim($str, '-');
    }
}
