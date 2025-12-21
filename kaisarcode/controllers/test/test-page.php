<?php
/**
 * Test suite for PageController
 */

$root = getenv('PROJECT_ROOT');
$core = getenv('CORE_ROOT');
if (!$root || !$core) {
    echo "[FAIL] PROJECT_ROOT or CORE_ROOT not set\n";
    exit(1);
}

// Bootstrap
define('DIR_APP', $root);
define('DIR_CORE', $core);
define('DIR_VAR', $root . '/var');

require_once $core . '/autoload.php';

autoload([
    $root . '/classes',
    $root . '/controllers',
    $root . '/models',
    $core . '/classes',
    $core . '/controllers',
    $core . '/models',
]);

// Initialize
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_METHOD'] = 'GET';
require_once DIR_APP . '/conf.php';
Controller::init();

class PageControllerTest
{
    private int $passed = 0;
    private int $failed = 0;

    public function run(): int
    {
        $this->testHome();
        $this->testNotFound();

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

    private function testHome(): void
    {
        // Test root path /
        ob_start();
        $res = PageController::handle(['/']);
        ob_get_clean();

        if ($res !== false && strpos($res, 'Inicio') !== false) {
            $this->pass("PageController::handle('/') returns 'Inicio' document");
        } else {
            $this->fail("PageController::handle('/') failed to return home document");
        }
    }

    private function testNotFound(): void
    {
        // Test non-existent path
        $res = PageController::handle(['/path-that-does-not-exist']);

        if ($res === false) {
            $this->pass("PageController::handle() returns false for missing paths");
        } else {
            $this->fail("PageController::handle() should return false for missing paths");
        }
    }
}

$test = new PageControllerTest();
exit($test->run());
