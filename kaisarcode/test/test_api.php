<?php
/**
 * API Endpoint Test Suite
 * Tests /api/doc endpoint responses
 */

$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/api/doc';

define('DIR_APP', dirname(__DIR__));
define('DIR_CORE', dirname(__DIR__) . '/../phproj');

ob_start();
require_once DIR_CORE . '/setup.php';
$output = ob_get_clean();

$passed = 0;
$failed = 0;

function test_pass($msg) {
    global $passed;
    $passed++;
    echo "PASS: $msg\n";
}

function test_fail($msg) {
    global $failed;
    $failed++;
    echo "FAIL: $msg\n";
}

// Parse JSON response
$data = json_decode($output, true);

if ($data === null) {
    test_fail('API response is not valid JSON');
    echo "Output: $output\n";
    exit(1);
}
test_pass('API returns valid JSON');

if ($data['status'] === 'ok') {
    test_pass("API returns status 'ok'");
} else {
    test_fail("API status is not 'ok'");
    exit(1);
}

if (is_array($data['result'])) {
    test_pass('API result is an array');
} else {
    test_fail('API result is not an array');
    exit(1);
}

echo "\nAPI Tests: $passed passed, $failed failed\n";
exit($failed > 0 ? 1 : 0);
