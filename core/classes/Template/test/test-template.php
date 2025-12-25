<?php
/**
 * Test suite for Template utility
 */

require_once __DIR__ . '/../Template.php';

class TemplateTest {
    private int $passed = 0;
    private int $failed = 0;
    private string $fixturesDir;
    private string $cacheDir;
    private Template $tpl;

    public function __construct() {
        $this->fixturesDir = __DIR__ . '/fixtures';
        $this->cacheDir = '/tmp/core-template-test-' . uniqid();
        mkdir($this->cacheDir, 0755, true);

        // Define constant for template includes
        if (!defined('FIXTURES_DIR')) {
            define('FIXTURES_DIR', $this->fixturesDir);
        }

        $this->tpl = new Template([
            'cache_dir' => $this->cacheDir,
            'cache_enabled' => true,
            'base_dir' => $this->fixturesDir
        ]);
    }

    public function run(): int {
        $this->testSimpleVariables();
        $this->testIncludes();
        $this->testBlocks();
        $this->testBlockArgs();
        $this->testCaching();
        $this->testMissingFile();

        $this->cleanup();
        return $this->failed;
    }

    private function pass(string $msg): void {
        echo "\033[0;32m[PASS]\033[0m $msg\n";
        $this->passed++;
    }

    private function fail(string $msg): void {
        echo "\033[0;31m[FAIL]\003[0m $msg\n";
        $this->failed++;
    }

    private function testSimpleVariables(): void {
        $output = $this->tpl->parse($this->fixturesDir . '/simple.html', [
            'title' => 'Test Title',
            'description' => 'Test Description'
        ]);

        if (
            strpos($output, '<h1>Test Title</h1>') !== false &&
            strpos($output, '<p>Test Description</p>') !== false
        ) {
            $this->pass('Simple variable replacement works');
        } else {
            $this->fail('Simple variable replacement failed');
        }
    }

    private function testIncludes(): void {
        $output = $this->tpl->parse($this->fixturesDir . '/with-includes.html', [
            'fixturesDir' => $this->fixturesDir,
            'title' => 'Page Title',
            'content' => 'Page Content',
            'year' => '2025'
        ]);

        if (
            strpos($output, '<header>') !== false &&
            strpos($output, 'Site Header') !== false &&
            strpos($output, '<footer>') !== false &&
            strpos($output, 'Copyright 2025') !== false
        ) {
            $this->pass('Template includes work');
        } else {
            $this->fail('Template includes failed');
        }
    }

    private function testBlocks(): void {
        $output = $this->tpl->parse($this->fixturesDir . '/with-blocks.html', []);

        if (
            strpos($output, '<!DOCTYPE html>') !== false &&
            strpos($output, '<title>Default Title</title>') !== false &&
            strpos($output, 'Default Content') !== false
        ) {
            $this->pass('Template blocks work');
        } else {
            $this->fail('Template blocks failed');
        }
    }

    private function testBlockArgs(): void {
        $output = $this->tpl->parse($this->fixturesDir . '/block-args.html', []);

        if (
            strpos($output, 'Name: Alice') !== false &&
            strpos($output, 'Role: Admin') !== false &&
            strpos($output, 'Name: Bob') !== false &&
            strpos($output, 'Role: User') !== false
        ) {
            $this->pass('Template block arguments work');
        } else {
            $this->fail('Template block arguments failed');
        }
    }

    private function testCaching(): void {
        // First parse - creates cache
        $this->tpl->parse($this->fixturesDir . '/simple.html', ['title' => 'Test', 'description' => 'Test']);

        // Check if cache files were created
        $cacheFiles = glob($this->cacheDir . '/*.php');

        if (count($cacheFiles) > 0) {
            $this->pass('Template caching works');
        } else {
            $this->fail('Template caching failed');
        }
    }

    private function testMissingFile(): void {
        try {
            $this->tpl->parse($this->fixturesDir . '/nonexistent.html', []);
            $this->fail('Missing file check failed (should have thrown exception)');
        } catch (\ValueError | \RuntimeException $e) {
            $this->pass('Missing file check works');
        }
    }

    private function cleanup(): void {
        // Clean up cache directory
        $files = glob($this->cacheDir . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        rmdir($this->cacheDir);
    }

    public function summary(): void {
        echo "\n";
        echo "Total: " . ($this->passed + $this->failed) . " | ";
        echo "\033[0;32mPassed: {$this->passed}\033[0m | ";
        echo "\033[0;31mFailed: {$this->failed}\033[0m\n";
    }
}

$test = new TemplateTest();
$result = $test->run();
$test->summary();

exit($result);
