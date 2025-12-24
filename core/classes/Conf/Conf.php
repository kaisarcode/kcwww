<?php
/**
 * Conf - Configuration container
 * Summary: Provides associative and nested key support for configuration management
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
 * Configuration container with associative and nested key support.
 */
class Conf
{
    private static array $conf = [];
    private static array $excl = [];

    /**
     * Set a single key or multiple values.
     *
     * @param string|array $key
     * @param mixed|null $value
     * @param bool $hide
     */
    public static function set(string|array $key, mixed $value = null, int|bool $hide = false): void
    {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                self::set($k, $v);
            }
            return;
        }
        $segments = explode('.', $key);
        $ref = &self::$conf;
        foreach ($segments as $segment) {
            if (!isset($ref[$segment]) || !is_array($ref[$segment])) {
                $ref[$segment] = [];
            }
            $ref = &$ref[$segment];
        }
        $ref = $value;
        $hide && self::$excl[] = $key;
    }

    /**
     * Retrieve a value by key, supporting dot notation.
     *
     * @param string $key
     * @return mixed
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $segments = explode('.', $key);
        $ref = self::$conf;
        foreach ($segments as $segment) {
            if (!is_array($ref) || !array_key_exists($segment, $ref)) {
                return $default;
            }
            $ref = $ref[$segment];
        }
        return $ref;
    }

    /**
     * Remove a value by key, supporting dot notation.
     *
     * @param string $key
     */
    public static function del(string $key): void
    {
        $segments = explode('.', $key);
        $last = array_pop($segments);
        $ref = &self::$conf;
        foreach ($segments as $segment) {
            if (!isset($ref[$segment]) || !is_array($ref[$segment])) {
                return;
            }
            $ref = &$ref[$segment];
        }
        unset($ref[$last]);
    }

    /**
     * Mark paths as excluded from all().
     *
     * @param string|array $paths
     */
    public static function exc(string|array $paths): void
    {
        $paths = is_array($paths) ? $paths : [$paths];
        self::$excl = array_unique(array_merge(self::$excl, $paths));
    }

    /**
     * Get the entire config array.
     *
     * @param bool $hidden
     * @return array
     */
    public static function all(int|bool $hidden = false): array
    {
        $data = self::$conf;
        if ($hidden) {
            return $data;
        }
        foreach (self::$excl as $path) {
            $segments = explode('.', $path);
            $ref = &$data;
            while (count($segments) > 1) {
                $segment = array_shift($segments);
                if (!isset($ref[$segment]) || !is_array($ref[$segment])) {
                    continue 2;
                }
                $ref = &$ref[$segment];
            }
            unset($ref[array_shift($segments)]);
        }
        return $data;
    }
}
