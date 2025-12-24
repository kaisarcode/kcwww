<?php
/**
 * Application Entry Point
 * Summary: Minimal entry point that bootstraps the application via setup.php
 *
 * Author:  KaisarCode
 * Website: https://kaisarcode.com
 * License: GNU GPL v3.0
 * License URL: https://www.gnu.org/licenses/gpl-3.0.html
 */

define('DIR_APP', __DIR__);
define('DIR_CORE', __DIR__);
require_once DIR_CORE . '/setup.php';
