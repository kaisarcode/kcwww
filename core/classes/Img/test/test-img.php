<?php
/**
 * Test suite for Img utility
 */

require_once __DIR__ . '/../Img.php';

class ImgTest {
    private int $passed = 0;
    private int $failed = 0;
    private string $tempDir;

    public function __construct() {
        $this->tempDir = sys_get_temp_dir() . '/core_img_test_' . uniqid();
        mkdir($this->tempDir);
    }

    public function run(): int {
        $this->checkRequirements();
        $this->testResizePng();
        // SVG testing requires rsvg-convert, might not be present.
        // If checking for binary fails, we skip specific tests or soft-fail.

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

    private function skip(string $msg): void {
        echo "\033[1;33m[SKIP]\033[0m $msg\n";
    }

    private function checkRequirements(): void {
        if (!extension_loaded('imagick') && !extension_loaded('gd')) {
            $this->fail("No ImageMagick or GD extension found");
            exit(1);
        }
    }

    private function testResizePng(): void {
        if (!extension_loaded('gd')) {
            $this->skip("GD not loaded, skipping generation of test image");
            return;
        }

        // Create a test 100x100 red png
        $srcFile = $this->tempDir . '/test.png';
        $img = imagecreatetruecolor(100, 100);
        $red = imagecolorallocate($img, 255, 0, 0);
        imagefill($img, 0, 0, $red);
        imagepng($img, $srcFile);
        imagedestroy($img);

        // Resize to 50x50
        try {
            $blob = Img::proc($srcFile, 50, 50, 'png');

            if (strlen($blob) > 0) {
                // Verify result is a PNG
                if (strpos($blob, 'PNG') !== false) {
                    $this->pass('Img::proc resized PNG successfully');
                } else {
                    $this->fail('Img::proc returned non-PNG data');
                }

                // Optional: Check dimensions if we can
                $res = imagecreatefromstring($blob);
                if ($res) {
                    if (imagesx($res) === 50 && imagesy($res) === 50) {
                        $this->pass('Img::proc dimensions correct');
                    } else {
                        $this->fail('Img::proc dimensions incorrect');
                    }
                    imagedestroy($res);
                }
            } else {
                $this->fail('Img::proc returned empty blob');
            }
        } catch (Throwable $e) {
            $this->fail('Img::proc threw exception: ' . $e->getMessage());
        }
    }

    private function cleanup(): void {
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

    public function summary(): void {
        echo "\n";
        echo "Total: " . ($this->passed + $this->failed) . " | ";
        echo "\033[0;32mPassed: {$this->passed}\033[0m | ";
        echo "\033[0;31mFailed: {$this->failed}\033[0m\n";
    }
}

$test = new ImgTest();
exit($test->run());
