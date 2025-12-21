<?php
/**
 * Test suite for DocModel
 * Summary: Tests DocModel CRUD operations against doc.sqlite.
 *
 * Author:  KaisarCode
 * Website: https://kaisarcode.com
 * License: GNU GPL v3.0
 */

$root = getenv('PROJECT_ROOT');
$core = getenv('CORE_ROOT');
if (!$root || !$core) {
    echo "[FAIL] PROJECT_ROOT or CORE_ROOT not set\n";
    exit(1);
}

// Bootstrap from core
define('DIR_APP', $root);
define('DIR_CORE', $core);
define('DIR_VAR', $root . '/var');

require_once $core . '/autoload.php';

autoload([
    $root . '/classes',
    $root . '/models',
    $core . '/classes',
    $core . '/models',
]);

// Define DSN for DocModel
define('DSN_DOC', 'sqlite:' . DIR_VAR . '/data/db/doc.sqlite');

class DocModelTest
{
    private int $passed = 0;
    private int $failed = 0;

    public function run(): int
    {
        $this->testConnection();
        $this->testAll();
        $this->testFind();
        $this->testPaginate();

        printf(
            "\nTotal: %d | Passed: %d | Failed: %d\n",
            $this->passed + $this->failed,
            $this->passed,
            $this->failed
        );

        return $this->failed > 0 ? 1 : 0;
    }

    private function pass(string $msg): void
    {
        printf("\033[0;32m[PASS]\033[0m %s\n", $msg);
        $this->passed++;
    }

    private function fail(string $msg): void
    {
        printf("\033[0;31m[FAIL]\033[0m %s\n", $msg);
        $this->failed++;
    }

    private function testConnection(): void
    {
        try {
            DocModel::init();
            $this->pass("DocModel::init() connects to database");
        } catch (Exception $e) {
            $this->fail("DocModel::init() failed: " . $e->getMessage());
        }
    }

    private function testAll(): void
    {
        $all = DocModel::all();
        if (is_array($all) && count($all) > 0) {
            $this->pass("DocModel::all() returns records");
        } else {
            $this->fail("DocModel::all() returned empty or invalid");
        }
    }

    private function testFind(): void
    {
        $all = DocModel::all();
        if (empty($all)) {
            $this->fail("No records to test find()");
            return;
        }

        $first = $all[0];
        $id = $first->id;
        $found = DocModel::find($id);

        if ($found && $found->id == $id) {
            $this->pass("DocModel::find() retrieves correct record");
        } else {
            $this->fail("DocModel::find() did not retrieve record");
        }
    }

    private function testPaginate(): void
    {
        $result = DocModel::paginate(1, 2);
        
        if (count($result['data']) === 2 && isset($result['meta']['total'])) {
            $this->pass("DocModel::paginate() returns 2 items and metadata");
        } else {
            $this->fail("DocModel::paginate() failed: count=" . count($result['data']));
        }

        $result2 = DocModel::paginate(1, 100);
        if ($result2['meta']['total'] === count($result2['data'])) {
            $this->pass("DocModel::paginate() returns all items when limit is high");
        } else {
            $this->fail("DocModel::paginate() high limit failed");
        }
    }
}

$test = new DocModelTest();
exit($test->run());
