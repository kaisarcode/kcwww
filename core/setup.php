<?php
/**
 * Setup - Application Bootstrap
 * Summary: Initializes autoloader, constants, configuration and routes.
 * Can be used standalone or as a core for child sites.
 *
 * Author:  KaisarCode
 * Website: https://kaisarcode.com
 * License: GNU GPL v3.0
 * License URL: https://www.gnu.org/licenses/gpl-3.0.html
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Determine if running as core for a child site
if (!defined('DIR_CORE')) {
    define('DIR_CORE', DIR_APP);
}

// Load Autoloader from core
require_once DIR_CORE . '/autoload.php';

// Register autoload directories
$autoloadPaths = [];
if (DIR_APP !== DIR_CORE) {
    $autoloadPaths[] = DIR_APP . '/classes';
    $autoloadPaths[] = DIR_APP . '/controllers';
    $autoloadPaths[] = DIR_APP . '/models';
}
$autoloadPaths[] = DIR_CORE . '/classes';
$autoloadPaths[] = DIR_CORE . '/controllers';
$autoloadPaths[] = DIR_CORE . '/models';
autoload($autoloadPaths);

// Initialize environment directories
define('DIR_VAR', DIR_APP . '/var');
Fs::mkdirp(DIR_VAR, 0775);
Fs::mkdirp(DIR_VAR . '/cache', 0775);
Fs::mkdirp(DIR_VAR . '/cache/img', 0775);
Fs::mkdirp(DIR_VAR . '/cache/tpl', 0775);
Fs::mkdirp(DIR_VAR . '/data/db', 0775);

// Load local environment variables
$envPath = null;
if (file_exists(DIR_VAR . '/.env')) {
    $envPath = DIR_VAR . '/.env';
} elseif (file_exists(DIR_CORE . '/var/.env')) {
    $envPath = DIR_CORE . '/var/.env';
}

if ($envPath) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') {
            continue;
        }
        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            $name = trim($parts[0]);
            $value = trim($parts[1]);
            putenv("$name=$value");
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}

// Development mode flag
define('DEVM', file_exists(DIR_VAR . '/dev'));

// Load core configuration
require_once DIR_CORE . '/conf.php';

// Load child configuration overrides
if (DIR_APP !== DIR_CORE && file_exists(DIR_APP . '/conf.php')) {
    require_once DIR_APP . '/conf.php';
}

// Initialize base controller
Controller::init();

// Load child routes
if (DIR_APP !== DIR_CORE && file_exists(DIR_APP . '/routes.php')) {
    require_once DIR_APP . '/routes.php';
}

// Load core routes
require_once DIR_CORE . '/routes.php';
