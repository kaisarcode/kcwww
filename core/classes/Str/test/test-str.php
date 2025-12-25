<?php
/**
 * Test suite for Str utility
 */

require_once __DIR__ . '/../Str.php';

class StrTest {
    private int $passed = 0;
    private int $failed = 0;

    public function run(): int {
        $this->testSanitize();
        $this->testTruncate();
        $this->testSlug();
        $this->testRandom();
        $this->testMin();
        $this->testMinCss();
        $this->testMinJs();
        $this->testNormalize();

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

    private function testSanitize(): void {
        $safe = Str::sanitize('<script>alert("xss")</script>');

        if (strpos($safe, '<script>') === false) {
            $this->pass('Sanitize works');
        } else {
            $this->fail('Sanitize failed');
        }
    }

    private function testTruncate(): void {
        $short = Str::truncate('This is a very long text', 10);

        if ($short === 'This is a ...') {
            $this->pass('Truncate works');
        } else {
            $this->fail('Truncate failed');
        }
    }

    private function testSlug(): void {
        $slug = Str::slug('Hello World! 123');

        if ($slug === 'hello-world-123') {
            $this->pass('Slug works');
        } else {
            $this->fail('Slug failed');
        }
    }

    private function testRandom(): void {
        $random = Str::random(32);

        if (strlen($random) === 32) {
            $this->pass('Random works');
        } else {
            $this->fail('Random failed');
        }
    }

    private function testMin(): void {
        $min = Str::min("Hello    World");

        if ($min === 'Hello World') {
            $this->pass('Min works');
        } else {
            $this->fail('Min failed');
        }
    }

    private function testMinCss(): void {
        $css = "body { margin: 0px; padding: 0.5em; }";
        $min = Str::minCss($css);

        if (strpos($min, ' {') === false && strpos($min, '0px') === false) {
            $this->pass('MinCss works');
        } else {
            $this->fail('MinCss failed: ' . $min);
        }
    }

    private function testMinJs(): void {
        $js = "function test() { // comment\n    return true; }";
        $min = Str::minJs($js);

        if (strpos($min, '// comment') === false && strpos($min, '    ') === false) {
            $this->pass('MinJs works');
        } else {
            $this->fail('MinJs failed: ' . $min);
        }
    }

    private function testNormalize(): void {
        $norm = Str::normalize('Test');

        if ($norm === 'Test') {
            $this->pass('Normalize works');
        } else {
            $this->fail('Normalize failed');
        }
    }

    public function summary(): void {
        echo "\n";
        echo "Total: " . ($this->passed + $this->failed) . " | ";
        echo "\033[0;32mPassed: {$this->passed}\033[0m | ";
        echo "\033[0;31mFailed: {$this->failed}\033[0m\n";
    }
}

$test = new StrTest();
$result = $test->run();
$test->summary();

exit($result);
