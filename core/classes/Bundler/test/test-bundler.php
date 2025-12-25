<?php
/**
 * Test suite for Bundler utility
 */

require_once __DIR__ . '/../Bundler.php';

class BundlerTest {
    private int $passed = 0;
    private int $failed = 0;
    private string $tempDir;

    public function __construct() {
        $this->tempDir = sys_get_temp_dir() . '/core_bundler_test_' . uniqid();
        mkdir($this->tempDir);
    }

    public function run(): int {
        $this->testCssBundling();
        $this->testJsBundling();

        $this->cleanup();
        $this->summary();
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

    private function testCssBundling(): void {
        $mainFile = $this->tempDir . '/style.css';
        $impFile = $this->tempDir . '/import.css';

        file_put_contents($impFile, ":root { --color: red; } .imp { color: var(--color); }");
        file_put_contents($mainFile, "@import url('import.css'); body { background: var(--color); }");

        $res = Bundler::css($mainFile);

        // Expected: import content inlined, vars resolved.
        // :root block might be stripped.
        // Result should contain ".imp { color: red; }" and "body { background: red; }"

        if (
            strpos($res, '.imp { color: red; }') !== false &&
            strpos($res, 'body { background: red; }') !== false
        ) {
            $this->pass('Bundler::css inlines imports and resolves vars');
        } else {
            $this->fail("Bundler::css failed. Got:\n$res");
        }
    }

    private function testJsBundling(): void {
        $mainFile = $this->tempDir . '/app.js';
        $modFile = $this->tempDir . '/mod.js';

        file_put_contents($modFile, "console.log('Mod'); export default Mod;");
        file_put_contents($mainFile, "import Mod from './mod.js'; console.log('App');");

        $res = Bundler::js($mainFile);

        if (
            strpos($res, "console.log('Mod');") !== false &&
            strpos($res, "console.log('App');") !== false
        ) {
            $this->pass('Bundler::js inlines imports');
        } else {
            $this->fail("Bundler::js failed content check");
        }

        if (strpos($res, 'export default') !== false) {
            $this->fail('Bundler::js failed to strip exports');
        } else {
            $this->pass('Bundler::js strips exports');
        }
    }

    private function cleanup(): void {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->tempDir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $fileinfo) {
            $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
            $todo($fileinfo->getRealPath());
        }

        rmdir($this->tempDir);
    }

    public function summary(): void {
        echo "\n";
        echo "Total: " . ($this->passed + $this->failed) . " | ";
        echo "\033[0;32mPassed: {$this->passed}\033[0m | ";
        echo "\033[0;31mFailed: {$this->failed}\033[0m\n";
    }
}

$test = new BundlerTest();
exit($test->run());
