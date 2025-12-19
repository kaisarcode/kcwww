<?php
/**
 * Bundler - Asset bundling utility
 * Summary: Provides methods to bundle and inline CSS and JavaScript files, resolving dependencies recursively.
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
 * Asset bundling utility
 */
class Bundler
{
    /**
     * Bundles CSS files by resolving @import statements and CSS variables.
     * Replaces @import url("file.css") with file content recursively.
     * Replaces var(--name) with :root definitions.
     *
     * @param string $file Path to entry CSS file
     * @return string Bundled CSS content
     */
    public static function css(string $file): string
    {
        $content = self::inlineCssImports($file);
        return self::resolveCssVars($content);
    }

    /**
     * Bundles JS files by resolving ES module imports.
     * Recursively replaces "import ... from 'file.js'" with file contents.
     * Strips "export default X" to create a single script.
     *
     * @param string $file Path to entry JS file
     * @return string Bundled JS content
     */
    public static function js(string $file): string
    {
        $baseDir = dirname($file);
        $str = file_get_contents($file);
        $pattern = '/import\s+[^;]*?["\'](.*?)["\']\s*;/';

        $str = preg_replace_callback(
            $pattern,
            function ($m) use ($baseDir) {
                $importPath = realpath($baseDir . '/' . $m[1]);
                if (!$importPath || !is_file($importPath)) {
                    return '';
                }
                // Recursively call js() on imported file to resolve its own imports
                // Note: The original Js implementation called self::replaceImports (recursive).
                // We need to allow recursion here, but 'js' strips exports too.
                // To be safe, let's process imports first, then strip exports at the end level?
                // Actually, the previous implementation did recursion on the REPLACEMENT logic.
                // We should separate the recursive inlining from the top-level specific processing if needed.
                // BUT, looking at the logic: each file needs imports resolved.
                // So calling self::js($importPath) works recursively.
                return self::js($importPath);
            },
            $str
        );

        // Strip exports
        $str = preg_replace('/export\s+default\s+[A-Za-z_][A-Za-z0-9_]*\s*;?/', '', $str);
        return $str;
    }

    /**
     * Internal: Inline CSS imports recursively
     */
    private static function inlineCssImports(string $file): string
    {
        $baseDir = dirname($file);
        $str = file_get_contents($file);
        $pattern = '/@import\s+url\(["\']?(.*?)["\']?\)\s*;/';

        return preg_replace_callback($pattern, function ($m) use ($baseDir) {
            $importPath = realpath($baseDir . '/' . $m[1]);
            if (!$importPath || !is_file($importPath))
                return '';
            return self::inlineCssImports($importPath);
        }, $str);
    }

    /**
     * Internal: Resolve CSS variables from :root
     */
    private static function resolveCssVars(string $str): string
    {
        $vars = [];
        $rootBlockPattern = '/:root\s*{(.*?)}/s';
        $varDefPattern = '/--([\w-]+)\s*:\s*([^;]+);/';
        $varUsePattern = '/var\(--([\w-]+)\)/';

        preg_match_all($rootBlockPattern, $str, $blocks, PREG_SET_ORDER);
        foreach ($blocks as $match) {
            $block = $match[1];
            preg_match_all($varDefPattern, $block, $definitions, PREG_SET_ORDER);
            foreach ($definitions as $def) {
                $vars[$def[1]] = trim($def[2]);
            }
            // Optional: Remove :root blocks? Old implementation did.
            $str = str_replace($match[0], '', $str);
        }

        $resolver = function ($m) use ($vars) {
            return $vars[$m[1]] ?? '';
        };

        return preg_replace_callback($varUsePattern, $resolver, $str);
    }
}
