<?php
/**
 * DocModel Test Suite
 * Tests CRUD operations for DocModel
 */

$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/test';

define('DIR_APP', dirname(__DIR__));
define('DIR_CORE', dirname(__DIR__) . '/../phproj');
require_once DIR_CORE . '/setup.php';

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

// Test 1: DocModel class exists
if (!class_exists('DocModel')) {
    test_fail('DocModel class not found');
    exit(1);
}
test_pass('DocModel class exists');

// Test 2: DocModel can be instantiated
try {
    $doc = new DocModel();
    test_pass('DocModel instantiated');
} catch (Throwable $e) {
    test_fail('DocModel instantiation failed: ' . $e->getMessage());
    exit(1);
}

// Test 3: Create and save a document
$doc->title = 'Test Document';
$doc->content = 'Test content for automated testing';
$id = $doc->save();

if ($id > 0) {
    test_pass("DocModel saved with ID: $id");
} else {
    test_fail('DocModel save failed');
    exit(1);
}

// Test 4: Find the document
$found = DocModel::find($id);
if ($found && $found->title === 'Test Document') {
    test_pass('DocModel find works');
} else {
    test_fail('DocModel find failed');
    exit(1);
}

// Test 5: Update the document
$found->title = 'Updated Title';
$found->save();
$updated = DocModel::find($id);
if ($updated && $updated->title === 'Updated Title') {
    test_pass('DocModel update works');
} else {
    test_fail('DocModel update failed');
    exit(1);
}

// Test 6: Delete the document
$updated->delete();
$deleted = DocModel::find($id);
if ($deleted === null) {
    test_pass('DocModel delete works');
} else {
    test_fail('DocModel delete failed');
    exit(1);
}

// Test 7: Verify auto-fields were set
$doc2 = DocModel::create(['title' => 'Auto Field Test']);
$id2 = $doc2->save();
$loaded = DocModel::find($id2);

if ($loaded->active == 1) {
    test_pass("auto-field 'active' set correctly");
} else {
    test_fail("auto-field 'active' not set");
    exit(1);
}

if (!empty($loaded->date_add)) {
    test_pass("auto-field 'date_add' set correctly");
} else {
    test_fail("auto-field 'date_add' not set");
    exit(1);
}

if (!empty($loaded->date_upd)) {
    test_pass("auto-field 'date_upd' set correctly");
} else {
    test_fail("auto-field 'date_upd' not set");
    exit(1);
}

// Cleanup
$loaded->delete();

echo "\nDocModel Tests: $passed passed, $failed failed\n";
exit($failed > 0 ? 1 : 0);
