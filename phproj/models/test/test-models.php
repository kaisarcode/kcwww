<?php
/**
 * Test suite for Models
 */

$root = getenv('PROJECT_ROOT');
if (!$root) {
    echo "[FAIL] PROJECT_ROOT not set\n";
    exit(1);
}

require_once $root . '/autoload.php';

autoload([
    $root . '/core/classes',
    $root . '/core/models',
]);

class ModelTest
{
    private int $passed = 0;
    private int $failed = 0;

    public function run(): int
    {
        $this->testDummySave();
        $this->testDummyHooks();
        $this->testDummyFind();
        $this->testDummyDelete();

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

    private function testDummySave(): void
    {
        $dummy = DummyModel::create(['name' => 'Test 1']);
        $id = $dummy->save();

        if ($id > 0) {
            $this->pass("DummyModel::save returns ID > 0");
        } else {
            $this->fail("DummyModel::save failed");
        }
    }

    private function testDummyHooks(): void
    {
        $dummy = DummyModel::create(['name' => 'Hook Test']);
        $dummy->save();

        if (!empty($dummy->created_at)) {
            $this->pass("beforeSave hook set created_at");
        } else {
            $this->fail("beforeSave hook did not set created_at");
        }

        $id = $dummy->getId();
        $loaded = DummyModel::find($id);

        if (!empty($loaded->formatted_date)) {
            $this->pass("afterLoad hook set formatted_date");
        } else {
            $this->fail("afterLoad hook did not set formatted_date");
        }
    }

    private function testDummyFind(): void
    {
        $all = DummyModel::all();
        if (count($all) >= 2) {
            $this->pass("DummyModel::all returns multiple records");
        } else {
            $this->fail("DummyModel::all returned " . count($all) . " records");
        }
    }

    private function testDummyDelete(): void
    {
        $dummy = DummyModel::create(['name' => 'To Delete']);
        $id = $dummy->save();
        $dummy->delete();

        $found = DummyModel::find($id);
        if ($found === null) {
            $this->pass("DummyModel::delete removes record");
        } else {
            $this->fail("DummyModel::delete did not remove record");
        }
    }
}

$test = new ModelTest();
exit($test->run());
