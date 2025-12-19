<?php
/**
 * Test suite for Fs utility
 */

require_once __DIR__.'/../Fs.php';

class FsTest
{
    private int $passed = 0;
    private int $failed = 0;
    private string $tempDir;

    public function __construct()
    {
        $this->tempDir = sys_get_temp_dir() . '/phproj_fs_test_' . uniqid();
        mkdir($this->tempDir);
    }

    public function run(): int
    {
        $this->testPutGet();
        $this->testMkdirp();
        $this->testLsFiles();
        $this->testLsDirs();

        $this->cleanup();
        $this->summary();
        return $this->failed;
    }

    private function pass(string $msg): void
    {
        echo "\033[0;32m[PASS]\033[0m $msg\n";
        $this->passed++;
    }

    private function fail(string $msg): void
    {
        echo "\033[0;31m[FAIL]\033[0m $msg\n";
        $this->failed++;
    }

    private function testPutGet(): void
    {
        $file = $this->tempDir . '/test.txt';
        $content = 'Hello Fs';

        // Test Put
        if (Fs::put($file, $content)) {
            $this->pass('Fs::put returns true');
        } else {
            $this->fail('Fs::put returned false');
        }

        // Test Get
        $read = Fs::get($file);
        if ($read === $content) {
            $this->pass('Fs::get retrieves content correctly');
        } else {
            $this->fail("Fs::get failed. Got: '$read', Expected: '$content'");
        }

        // Test Get default
        $def = Fs::get($this->tempDir . '/nonexistent', 'def');
        if ($def === 'def') {
            $this->pass('Fs::get returns default on missing file');
        } else {
            $this->fail("Fs::get default failed. Got: '$def'");
        }

        // Test Put Append
        Fs::put($file, ' World', null, true);
        $read = Fs::get($file);
        if ($read === 'Hello Fs World') {
            $this->pass('Fs::put append works');
        } else {
            $this->fail('Fs::put append failed');
        }
    }

    private function testMkdirp(): void
    {
        $deepDir = $this->tempDir . '/a/b/c';

        if (Fs::mkdirp($deepDir)) {
            $this->pass('Fs::mkdirp returns true');
        } else {
            $this->fail('Fs::mkdirp returned false');
        }

        if (is_dir($deepDir)) {
            $this->pass('Fs::mkdirp creates directory recursively');
        } else {
            $this->fail('Fs::mkdirp failed to create directory');
        }
    }

    private function testLsFiles(): void
    {
        $dir = $this->tempDir . '/ls_files';
        Fs::mkdirp($dir . '/sub');
        Fs::put($dir . '/f1.txt', '');
        Fs::put($dir . '/f2.txt', '');
        Fs::put($dir . '/sub/f3.txt', '');

        $files = Fs::lsFiles($dir);
        if (count($files) === 2) {
            $this->pass('Fs::lsFiles non-recursive count correct');
        } else {
            $this->fail('Fs::lsFiles non-recursive count failed: ' . count($files));
        }

        $filesRec = Fs::lsFiles($dir, true);
        if (count($filesRec) === 3) {
            $this->pass('Fs::lsFiles recursive count correct');
        } else {
            $this->fail('Fs::lsFiles recursive count failed: ' . count($filesRec));
        }
    }

    private function testLsDirs(): void
    {
        $dir = $this->tempDir . '/ls_dirs';
        Fs::mkdirp($dir . '/d1');
        Fs::mkdirp($dir . '/d2');
        Fs::mkdirp($dir . '/d2/sub');

        $dirs = Fs::lsDirs($dir);
        // Expect d1, d2
        if (count($dirs) === 2) {
            $this->pass('Fs::lsDirs non-recursive count correct');
        } else {
            $this->fail('Fs::lsDirs non-recursive count failed: ' . count($dirs));
        }

        $dirsRec = Fs::lsDirs($dir, 1);
        // Expect d1, d2, d2/sub
        if (count($dirsRec) === 3) {
            $this->pass('Fs::lsDirs recursive count correct');
        } else {
            $this->fail('Fs::lsDirs recursive count failed: ' . count($dirsRec));
        }
    }

    private function cleanup(): void
    {
        // Recursive delete temp dir
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

    public function summary(): void
    {
        echo "\n";
        echo "Total: " . ($this->passed + $this->failed) . " | ";
        echo "\033[0;32mPassed: {$this->passed}\033[0m | ";
        echo "\033[0;31mFailed: {$this->failed}\033[0m\n";
    }
}

$test = new FsTest();
exit($test->run());
