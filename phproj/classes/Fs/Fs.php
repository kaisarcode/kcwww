<?php
/**
 * Fs - Filesystem utilities
 * Summary: Provides utilities for file and directory operations
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
 * Filesystem utilities
 */
class Fs
{

    /**
     * File get contents wrapper
     *
     * @param string $fl
     * @param mixed $default value returned if file cannot be read
     * @return string|mixed
     */
    static function get(string $fl, $default = null)
    {
        $res = @file_get_contents($fl);
        return $res === false ? $default : $res;
    }

    /**
     * List folders in directory
     *
     * @param string $dir
     * @param int $rec
     * @return array
     */
    static function lsDirs(string $dir, int $rec = 0): array
    {
        $arr = array();
        $dir = new \DirectoryIterator($dir);
        foreach ($dir as $d) {
            $pth = $d->getPathname();
            $d->isDir() && !$d->isDot()
                && array_push($arr, $pth) &&
                $rec && $arr = array_merge
                ($arr, self::lsDirs($pth));
        }
        return $arr;
    }

    /**
     * List files in directory
     *
     * @param string $dir
     * @param bool $rec
     * @return array
     */
    static function lsFiles(string $dir, bool $rec = false): array
    {
        $arr = array();
        if (!is_dir($dir)) {
            return $arr;
        }
        $dir = new \DirectoryIterator($dir);
        foreach ($dir as $d) {
            $pth = $d->getPathname();
            if ($d->isFile()) {
                array_push($arr, $pth);
            } elseif ($d->isDir() && !$d->isDot() && $rec) {
                $arr = array_merge($arr, self::lsFiles($pth, $rec));
            }
        }
        return $arr;
    }

    /**
     * Recursively create directory with permissions
     *
     * @param string $dir
     * @param int|null $perms
     * @return bool
     */
    static function mkdirp(string $dir, ?int $perms = null): bool
    {
        if (is_dir($dir)) {
            return true;
        }
        $perms = $perms ?? 0777;
        $ok = mkdir($dir, $perms, true);
        if ($ok) {
            $parts = explode(DIRECTORY_SEPARATOR, $dir);
            $path = '';
            foreach ($parts as $part) {
                if ($part === '') {
                    $path .= DIRECTORY_SEPARATOR;
                    continue;
                }
                $path .= ($path === DIRECTORY_SEPARATOR ? '' : DIRECTORY_SEPARATOR) . $part;
                if (is_dir($path)) {
                    @chmod($path, $perms);
                }
            }
        }
        return $ok;
    }

    /**
     * Write contents to file, always using default permissions if not set
     *
     * @param string $file
     * @param string $contents
     * @param int|null $perms
     * @param bool $append
     * @return bool
     */
    static function put(string $file, string $contents, ?int $perms = null, bool $append = false): bool
    {
        $perms = $perms ?? 0777;
        $dir = dirname($file);
        self::mkdirp($dir, $perms);
        $mode = $append ? FILE_APPEND : 0;
        $ok = file_put_contents($file, $contents, $mode) !== false;
        $ok && @chmod($file, $perms);
        return $ok;
    }
}
