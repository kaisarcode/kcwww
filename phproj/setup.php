<?php
/**
 * Setup - Application Bootstrap
 * Summary: Initializes autoloader, constants, configuration and routes for phproj.
 * Can be used standalone or as a core for child sites.
 *
 * Author:  KaisarCode
 * Website: https://kaisarcode.com
 * License: GNU GPL v3.0
 * License URL: https://www.gnu.org/licenses/gpl-3.0.html
 */

// Determine if running as core for a child site
// CORE is defined by child sites, ROOT is always the child's path
if (!defined('CORE')) {
    define('CORE', ROOT);
}

// Load Autoloader from core
require_once CORE . '/autoload.php';

// Register autoload directories (child paths first for override capability)
$autoloadPaths = [];
if (ROOT !== CORE) {
    $autoloadPaths[] = ROOT . '/classes';
    $autoloadPaths[] = ROOT . '/controllers';
    $autoloadPaths[] = ROOT . '/models';
}
$autoloadPaths[] = CORE . '/classes';
$autoloadPaths[] = CORE . '/controllers';
$autoloadPaths[] = CORE . '/models';
autoload($autoloadPaths);

// Define directory constants (use child paths for runtime data)
define('VIEWS', CORE . '/views');
define('APP_VAR', ROOT . '/var');
define('APP_CACHE', APP_VAR . '/cache');

// Child can override views
if (ROOT !== CORE && is_dir(ROOT . '/views')) {
    define('VIEWS_OVERRIDE', ROOT . '/views');
}

// Initialize environment directories
Fs::mkdirp(APP_VAR, 0775);
Fs::mkdirp(APP_CACHE, 0775);
Fs::mkdirp(APP_CACHE . '/img', 0775);
Fs::mkdirp(APP_CACHE . '/tpl', 0775);

// Development mode flag
define('DEVM', file_exists(APP_VAR . '/dev'));

// Load core configuration
require_once CORE . '/conf.php';

// Load child configuration overrides
if (ROOT !== CORE && file_exists(ROOT . '/conf.php')) {
    require_once ROOT . '/conf.php';
}

// Initialize base controller
Controller::init();

// Load core routes
require_once CORE . '/routes.php';

// Load child routes (additional or override)
if (ROOT !== CORE && file_exists(ROOT . '/routes.php')) {
    require_once ROOT . '/routes.php';
}
