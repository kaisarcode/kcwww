<?php
/**
 * Test suite for Models
 * Summary: Tests Model base class functionality using an in-memory SQLite database.
 *
 * Author:  KaisarCode
 * Website: https://kaisarcode.com
 * License: GNU GPL v3.0
 */

$root = getenv('PROJECT_ROOT');
if (!$root) {
    echo "[FAIL] PROJECT_ROOT not set\n";
    exit(1);
}

// Simulate minimal bootstrap
define('DIR_APP', $root);
define('DIR_CORE', $root);

require_once $root . '/autoload.php';

autoload([
    $root . '/classes',
    $root . '/models',
]);

// Create test model dynamically
class TestModel extends Model {
    protected static string $table = 'testitems';
}

class ModelTest {
    private int $passed = 0;
    private int $failed = 0;

    public function run(): int {
        // Initialize with in-memory database
        TestModel::init('sqlite::memory:');

        // Create test table
        $pdo = new PDO('sqlite::memory:');
        $pdo->exec('CREATE TABLE testitems (id INTEGER PRIMARY KEY, name TEXT, created_at TEXT)');
        TestModel::init('sqlite::memory:');

        // Re-init with the same PDO is tricky with :memory:, so let's use a temp file
        $dbPath = '/tmp/core-test-model.db';
        @unlink($dbPath);
        $pdo = new PDO("sqlite:$dbPath");
        $pdo->exec('CREATE TABLE testitems (id INTEGER PRIMARY KEY, name TEXT, created_at TEXT)');
        $pdo = null;

        TestModel::init("sqlite:$dbPath");

        $this->testSave();
        $this->testFind();
        $this->testAll();
        $this->testPaginate();
        $this->testDelete();

        // Cleanup
        @unlink($dbPath);

        printf(
            "\nTotal: %d | Passed: %d | Failed: %d\n",
            $this->passed + $this->failed,
            $this->passed,
            $this->failed
        );

        return $this->failed > 0 ? 1 : 0;
    }

    private function pass(string $msg): void {
        printf("\033[0;32m[PASS]\033[0m %s\n", $msg);
        $this->passed++;
    }

    private function fail(string $msg): void {
        printf("\033[0;31m[FAIL]\033[0m %s\n", $msg);
        $this->failed++;
    }

    private function testSave(): void {
        $model = TestModel::create(['name' => 'Test Item']);
        $id = $model->save();

        if ($id > 0) {
            $this->pass("Model::save() returns ID > 0");
        } else {
            $this->fail("Model::save() failed");
        }
    }

    private function testFind(): void {
        $model = TestModel::create(['name' => 'Find Me']);
        $id = $model->save();

        $found = TestModel::find($id);
        if ($found && $found->name === 'Find Me') {
            $this->pass("Model::find() retrieves correct record");
        } else {
            $this->fail("Model::find() did not retrieve record");
        }
    }

    private function testAll(): void {
        $all = TestModel::all();
        if (count($all) >= 2) {
            $this->pass("Model::all() returns multiple records");
        } else {
            $this->fail("Model::all() returned " . count($all) . " records");
        }
    }

    private function testPaginate(): void {
        // Add more items to have enough for pagination testing
        for ($i = 0; $i < 5; $i++) {
            TestModel::create(['name' => "Item $i"])->save();
        }

        $result = TestModel::paginate(1, 2);

        if (count($result['result']) === 2 && $result['pagination']['total'] >= 7) {
            $this->pass("Model::paginate() returns correct number of items and metadata");
        } else {
            $this->fail("Model::paginate() failed: count=" . count($result['result']) . ", total=" . $result['pagination']['total']);
        }

        $result2 = TestModel::paginate(2, 2);
        if ($result2['pagination']['total'] >= 7 && count($result2['result']) >= 2) {
            $this->pass("Model::paginate() handles second page correctly");
        } else {
            $this->fail("Model::paginate() second page failed");
        }
    }

    private function testDelete(): void {
        $model = TestModel::create(['name' => 'Delete Me']);
        $id = $model->save();
        $model->delete();

        $found = TestModel::find($id);
        if ($found === null) {
            $this->pass("Model::delete() removes record");
        } else {
            $this->fail("Model::delete() did not remove record");
        }
    }
}

$test = new ModelTest();
exit($test->run());
