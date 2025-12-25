<?php
/**
 * Fs - Filesystem utilities
 * Summary: Provides utilities for file and directory operations.
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
 * Filesystem utilities.
 */
class Fs {
    /**
     * File get contents wrapper.
     *
     * @param string $fl      File path.
     * @param mixed  $default Value returned if file cannot be read.
     *
     * @return string|mixed File contents or default.
     */
    public static function get(string $fl, mixed $default = null) {
        $res = @file_get_contents($fl);
        return $res === false ? $default : $res;
    }

    /**
     * List folders in directory.
     *
     * @param string  $dir Directory path.
     * @param integer $rec Recursion flag.
     *
     * @return array Array of directory paths.
     */
    public static function lsDirs(string $dir, int $rec = 0): array {
        $arr = [];
        $dirIterator = new \DirectoryIterator($dir);
        foreach ($dirIterator as $d) {
            $pth = $d->getPathname();
            if ($d->isDir() && !$d->isDot()) {
                $arr[] = $pth;
                if ($rec) {
                    $arr = array_merge($arr, self::lsDirs($pth));
                }
            }
        }
        return $arr;
    }

    /**
     * List files in directory.
     *
     * @param string  $dir Directory path.
     * @param boolean $rec Recursive flag.
     *
     * @return array Array of file paths.
     */
    public static function lsFiles(string $dir, bool $rec = false): array {
        $arr = [];
        if (!is_dir($dir)) {
            return $arr;
        }
        $dirIterator = new \DirectoryIterator($dir);
        foreach ($dirIterator as $d) {
            $pth = $d->getPathname();
            if ($d->isFile()) {
                $arr[] = $pth;
            } elseif ($d->isDir() && !$d->isDot() && $rec) {
                $arr = array_merge($arr, self::lsFiles($pth, $rec));
            }
        }
        return $arr;
    }

    /**
     * Recursively create directory with permissions.
     *
     * @param string       $dir   Directory path.
     * @param integer|null $perms Permissions.
     *
     * @return boolean True on success.
     */
    public static function mkdirp(string $dir, ?int $perms = null): bool {
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
                $sep = $path === DIRECTORY_SEPARATOR ? '' : DIRECTORY_SEPARATOR;
                $path .= $sep . $part;
                if (is_dir($path)) {
                    @chmod($path, $perms);
                }
            }
        }
        return $ok;
    }

    /**
     * Write contents to file with default permissions.
     *
     * @param string       $file     File path.
     * @param string       $contents File contents.
     * @param integer|null $perms    Permissions.
     * @param boolean      $append   Append mode.
     *
     * @return boolean True on success.
     */
    public static function put(
        string $file,
        string $contents,
        ?int $perms = null,
        bool $append = false
    ): bool {
        $perms = $perms ?? 0777;
        $dir = dirname($file);
        self::mkdirp($dir, $perms);
        $mode = $append ? FILE_APPEND : 0;
        $ok = file_put_contents($file, $contents, $mode) !== false;
        $ok && @chmod($file, $perms);
        return $ok;
    }
}
