<?php
/**
 * Test suite for Cmd utility
 */

require_once __DIR__ . '/../Cmd.php';

class CmdTest {
    private int $passed = 0;
    private int $failed = 0;
    private string $testCmd;

    public function __construct() {
        // Use /bin/echo as a safe test command
        $this->testCmd = '/bin/echo';
    }

    public function run(): int {
        $this->testRegister();
        $this->testExec();
        $this->testRun();
        $this->testTest();
        $this->testWhitelist();
        $this->testArgumentValidation();
        $this->testInvalidCommand();

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

    private function testRegister(): void {
        Cmd::clearWhitelist();
        Cmd::register('echo', $this->testCmd);

        if (Cmd::isWhitelisted('echo')) {
            $this->pass('Register command works');
        } else {
            $this->fail('Register command failed');
        }
    }

    private function testExec(): void {
        Cmd::clearWhitelist();
        Cmd::register('echo', $this->testCmd);

        $result = Cmd::exec('echo', ['test']);

        if ($result['output'] === 'test' && $result['exit_code'] === 0) {
            $this->pass('Exec command works');
        } else {
            $this->fail('Exec command failed');
        }
    }

    private function testRun(): void {
        Cmd::clearWhitelist();
        Cmd::register('echo', $this->testCmd);

        $output = Cmd::run('echo', ['hello']);

        if ($output === 'hello') {
            $this->pass('Run command works');
        } else {
            $this->fail('Run command failed');
        }
    }

    private function testTest(): void {
        Cmd::clearWhitelist();
        Cmd::register('echo', $this->testCmd);

        $success = Cmd::test('echo', ['test']);

        if ($success === true) {
            $this->pass('Test command works');
        } else {
            $this->fail('Test command failed');
        }
    }

    private function testWhitelist(): void {
        Cmd::clearWhitelist();
        Cmd::register('echo', $this->testCmd);
        Cmd::register('test', '/bin/test');

        $whitelist = Cmd::getWhitelist();

        if (count($whitelist) === 2 && in_array('echo', $whitelist) && in_array('test', $whitelist)) {
            $this->pass('Whitelist management works');
        } else {
            $this->fail('Whitelist management failed');
        }
    }

    private function testArgumentValidation(): void {
        Cmd::clearWhitelist();
        Cmd::register('echo', $this->testCmd, ['allowed']);

        try {
            // This should work
            Cmd::run('echo', ['allowed']);

            // This should throw exception
            try {
                Cmd::run('echo', ['notallowed']);
                $this->fail('Argument validation failed (should have thrown exception)');
            } catch (\RuntimeException $e) {
                $this->pass('Argument validation works');
            }
        } catch (\Exception $e) {
            $this->fail('Argument validation failed: ' . $e->getMessage());
        }
    }

    private function testInvalidCommand(): void {
        Cmd::clearWhitelist();

        try {
            Cmd::run('nonexistent', []);
            $this->fail('Invalid command check failed (should have thrown exception)');
        } catch (\RuntimeException $e) {
            $this->pass('Invalid command check works');
        }
    }

    public function summary(): void {
        echo "\n";
        echo "Total: " . ($this->passed + $this->failed) . " | ";
        echo "\033[0;32mPassed: {$this->passed}\033[0m | ";
        echo "\033[0;31mFailed: {$this->failed}\033[0m\n";
    }
}

$test = new CmdTest();
$result = $test->run();
$test->summary();

exit($result);
