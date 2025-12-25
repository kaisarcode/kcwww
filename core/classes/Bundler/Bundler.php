<?php
/**
 * Bundler - Asset bundling utility
 * Summary: Provides methods to bundle and inline CSS and JavaScript files.
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
 * Asset bundling utility.
 *
 * Uses Template engine to pre-process assets, allowing template syntax
 * like {{@ DIR_CORE }} for cross-project imports.
 */
class Bundler {
    /**
     * Gets template data for asset preprocessing.
     *
     * @return array Data variables available in assets.
     */
    private static function getTemplateData(): array {
        return [
            'DIR_CORE' => defined('DIR_CORE') ? DIR_CORE : '',
            'DIR_APP' => defined('DIR_APP') ? DIR_APP : '',
        ];
    }

    /**
     * Pre-processes file content through Template.
     *
     * @param string $file Path to file.
     *
     * @return string Processed content.
     */
    private static function preprocess(string $file): string {
        $tpl = new Template(['cache_enabled' => false]);
        return $tpl->parse($file, self::getTemplateData());
    }

    /**
     * Bundles CSS files by resolving @import and CSS variables.
     *
     * @param string $file Path to entry CSS file.
     *
     * @return string Bundled CSS content.
     */
    public static function css(string $file): string {
        $content = self::inlineCssImports($file);
        return self::resolveCssVars($content);
    }

    /**
     * Bundles JS files by resolving ES module imports.
     *
     * @param string $file Path to entry JS file.
     *
     * @return string Bundled JS content.
     */
    public static function js(string $file): string {
        $baseDir = dirname($file);
        $str = self::preprocess($file);
        $pattern = '/import\s+[^;]*?["\'](.+?)["\']\s*;/';

        $str = preg_replace_callback(
            $pattern,
            function ($m) use ($baseDir) {
                $path = $m[1];

                // Handle absolute vs relative paths
                if (str_starts_with($path, '/')) {
                    $importPath = realpath($path);
                } else {
                    $importPath = realpath($baseDir . '/' . $path);
                }

                if (!$importPath || !is_file($importPath)) {
                    return '';
                }
                return self::js($importPath);
            },
            $str
        );

        // Strip exports
        $str = preg_replace(
            '/export\s+default\s+[A-Za-z_][A-Za-z0-9_]*\s*;?/',
            '',
            $str
        );
        return $str;
    }

    /**
     * Internal inline CSS imports recursively.
     *
     * @param string $file Path to CSS file.
     *
     * @return string Inlined CSS content.
     */
    private static function inlineCssImports(string $file): string {
        $baseDir = dirname($file);
        $str = self::preprocess($file);
        $pattern = '/@import\s+url\(["\']?(.+?)["\']?\)\s*;/';

        return preg_replace_callback($pattern, function ($m) use ($baseDir) {
            $path = $m[1];

            // Handle absolute vs relative paths
            if (str_starts_with($path, '/')) {
                $importPath = realpath($path);
            } else {
                $importPath = realpath($baseDir . '/' . $path);
            }

            if (!$importPath || !is_file($importPath)) {
                return '';
            }
            return self::inlineCssImports($importPath);
        }, $str);
    }

    /**
     * Internal resolve CSS variables from root.
     *
     * @param string $str CSS content.
     *
     * @return string Resolved CSS content.
     */
    private static function resolveCssVars(string $str): string {
        $vars = [];
        $rootBlockPattern = '/:root\s*{(.+?)}/s';
        $varDefPattern = '/--([\w-]+)\s*:\s*([^;]+);/';
        $varUsePattern = '/var\(--([\w-]+)\)/';

        preg_match_all($rootBlockPattern, $str, $blocks, PREG_SET_ORDER);
        foreach ($blocks as $match) {
            $block = $match[1];
            preg_match_all($varDefPattern, $block, $definitions, PREG_SET_ORDER);
            foreach ($definitions as $def) {
                $vars[$def[1]] = trim($def[2]);
            }
            $str = str_replace($match[0], '', $str);
        }

        $resolver = function ($m) use ($vars) {
            return $vars[$m[1]] ?? '';
        };

        return preg_replace_callback($varUsePattern, $resolver, $str);
    }
}
