<?php
/**
 * Configuration Registry
 * Summary: Central configuration definitions for the core application.
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
    'app.id' => 'core',
    'app.name' => 'KaisarCode Core',
    'page.name' => 'KaisarCode Core',
    'app.desc' => 'Portable PHP Utility Toolkit',
    'app.keywords' => 'php, utilities, portable, zero-dependency, kaisarcode',
    'app.lang' => 'en',
    'app.color' => '#1a1a1a',
    'app.cache.ver' => '1.0.0',
    'app.dev' => DEVM,

    // Paths for template access
    'VIEWS' => DIR_CORE . '/views',

    // Assets setup
    'assets.css.entry' => DIR_CORE . '/views/css/styles.css',
    'assets.js.entry' => DIR_CORE . '/views/js/script.js',
    'assets.worker.entry' => DIR_CORE . '/views/worker/worker.js',

    // Image/Icon source directories
    'app.image.src_dir' => DIR_CORE . '/views/img',
    'app.icon.src' => DIR_CORE . '/views/img/app.svg',
    'app.favicon.src' => DIR_CORE . '/views/img/ico.svg',

    // Caching
    'app.image.cache_dir' => DIR_VAR . '/cache/img',
    'tpl.conf.cache_dir' => DIR_VAR . '/cache/tpl',
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
    ],
    // API Security
    'api.secret' => getenv('KC_API_SECRET') ?: 'changeme'
]);
