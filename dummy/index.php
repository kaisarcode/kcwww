<?php
/**
 * Dummy Site - Entry Point
 * Summary: Child site extending core core.
 *
 * Author:  KaisarCode
 * Website: https://kaisarcode.com
 * License: GNU GPL v3.0
 * License URL: https://www.gnu.org/licenses/gpl-3.0.html
 */

define('DIR_APP', __DIR__);
define('DIR_CORE', dirname(__DIR__) . '/core');
require_once DIR_CORE . '/setup.php';
