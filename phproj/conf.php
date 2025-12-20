<?php
/**
 * Configuration Registry
 * Summary: Central configuration definitions for the phproj application.
 *
 * This file defines all application settings via Conf::set(). It is loaded
 * after setup.php to ensure directory constants (VIEWS, APP_CACHE, DEVM)
 * are available.
 *
 * Author:  KaisarCode
 * Website: https://kaisarcode.com
 * License: GNU GPL v3.0
 * License URL: https://www.gnu.org/licenses/gpl-3.0.html
 */

// Application Configuration
Conf::set([
    'app.id' => 'phproj',
    'app.name' => 'PHProj',
    'page.name' => 'PHProj',
    'app.desc' => 'Portable PHP Utility Toolkit',
    'app.keywords' => 'php, utilities, portable, zero-dependency, kaisarcode',
    'app.lang' => 'en',
    'app.color' => '#1a1a1a',
    'app.cache.ver' => '1.0.0',
    'app.dev' => DEVM,

    // Paths for template access
    'VIEWS' => VIEWS,

    // Assets setup
    'assets.css.entry' => VIEWS . '/css/styles.css',
    'assets.js.entry' => VIEWS . '/js/script.js',
    'assets.worker.entry' => VIEWS . '/worker/worker.js',

    // Image/Icon source directories
    'app.image.src_dir' => VIEWS . '/img',
    'app.icon.src' => VIEWS . '/img/app.svg',
    'app.favicon.src' => VIEWS . '/img/ico.svg',

    // Caching
    'app.image.cache_dir' => APP_CACHE . '/img',
    'tpl.conf.cache_dir' => APP_CACHE . '/tpl',
    'tpl.conf.cache_enabled' => !DEVM,

    // PWA settings
    'app.icon.sizes' => [32, 192, 512],
    'app.screenshots' => [
        [
            'src' => '/img/screenshot-wide-w800.png',
            'sizes' => '800x600',
            'type' => 'image/png',
            'form_factor' => 'wide'
        ],
        [
            'src' => '/img/screenshot-narrow-w400.png',
            'sizes' => '400x800',
            'type' => 'image/png',
            'form_factor' => 'narrow'
        ]
    ]
]);
