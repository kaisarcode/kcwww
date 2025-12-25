<?php
/**
 * Autoloader - Flexible class autoloader
 * Summary: Scans directories for class files with PSR-4 and recursive fallback.
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
 * Flexible autoloader for projects with or without PSR-4 structure.
 *
 * Scans multiple base directories in two phases:
 * 1. Attempts direct PSR-4-style loading.
 * 2. If that fails and recursive is true searches recursively.
 *
 * @param array   $baseDirs  List of root paths to search in.
 * @param boolean $recursive Whether to enable recursive fallback search.
 *
 * @return void
 */
function autoload(array $baseDirs, bool $recursive = true): void {
    $ds = DIRECTORY_SEPARATOR;
    $baseDirs = array_map(
        fn(string $dir): string => rtrim($dir, $ds),
        $baseDirs
    );

    spl_autoload_register(function (string $className) use ($baseDirs, $ds, $recursive): void {
        $relative = str_replace('\\', $ds, $className) . '.php';

        // PSR-4 style
        foreach ($baseDirs as $base) {
            $file = $base . $ds . $relative;
            if (is_file($file)) {
                include $file;
                return;
            }
        }

        // Recursive search fallback
        if (!$recursive) {
            return;
        }

        foreach ($baseDirs as $base) {
            if (!is_dir($base)) {
                continue;
            }
            $it = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($base),
                RecursiveIteratorIterator::LEAVES_ONLY
            );
            foreach ($it as $file) {
                if (!$file->isFile()) {
                    continue;
                }
                if ($file->getBasename('.php') === $className) {
                    include $file->getPathname();
                    return;
                }
            }
        }
    });
}
