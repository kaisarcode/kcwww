<?php
/**
 * Test suite for Conf utility
 */

require_once __DIR__ . '/../Conf.php';

class ConfTest {
    private int $passed = 0;
    private int $failed = 0;

    public function run(): int {
        $this->testSetGet();
        $this->testNested();
        $this->testDefault();
        $this->testDelete();
        $this->testHidden();
        $this->testArray();

        return $this->failed;
    }

    private function pass(string $msg): void {
        echo "\033[0;32m[PASS]\033[0m $msg\n";
        $this->passed++;
    }

    private function fail(string $msg): void {
        echo "\033[0;31m[FAIL]\033[0m $msg\n";
        $this->failed++;
    }

    private function testSetGet(): void {
        Conf::set('test.key', 'value');
        $val = Conf::get('test.key');

        if ($val === 'value') {
            $this->pass('Set/Get works');
        } else {
            $this->fail('Set/Get failed');
        }
    }

    private function testNested(): void {
        Conf::set('app.db.host', 'localhost');
        Conf::set('app.db.port', 3306);

        $host = Conf::get('app.db.host');
        $port = Conf::get('app.db.port');

        if ($host === 'localhost' && $port === 3306) {
            $this->pass('Nested keys work');
        } else {
            $this->fail('Nested keys failed');
        }
    }

    private function testDefault(): void {
        $val = Conf::get('nonexistent', 'default');

        if ($val === 'default') {
            $this->pass('Default value works');
        } else {
            $this->fail('Default value failed');
        }
    }

    private function testDelete(): void {
        Conf::set('temp.key', 'temp');
        Conf::del('temp.key');
        $val = Conf::get('temp.key');

        if ($val === null) {
            $this->pass('Delete works');
        } else {
            $this->fail('Delete failed');
        }
    }

    private function testHidden(): void {
        Conf::set('secret', 'password', true);
        $all = Conf::all();
        $allWithHidden = Conf::all(true);

        if (!isset($all['secret']) && isset($allWithHidden['secret'])) {
            $this->pass('Hidden values work');
        } else {
            $this->fail('Hidden values failed');
        }
    }

    private function testArray(): void {
        Conf::set(['key1' => 'val1', 'key2' => 'val2']);

        $val1 = Conf::get('key1');
        $val2 = Conf::get('key2');

        if ($val1 === 'val1' && $val2 === 'val2') {
            $this->pass('Array set works');
        } else {
            $this->fail('Array set failed');
        }
    }

    public function summary(): void {
        echo "\n";
        echo "Total: " . ($this->passed + $this->failed) . " | ";
        echo "\033[0;32mPassed: {$this->passed}\033[0m | ";
        echo "\033[0;31mFailed: {$this->failed}\033[0m\n";
    }
}

$test = new ConfTest();
$result = $test->run();
$test->summary();

exit($result);
