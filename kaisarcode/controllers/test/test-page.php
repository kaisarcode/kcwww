<?php
/**
 * Test suite for PageController
 * Summary: Tests PageController request handling.
 *
 * Author:  KaisarCode
 * Website: https://kaisarcode.com
 * License: GNU GPL v3.0
 * License URL: https://www.gnu.org/licenses/gpl-3.0.html
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

/**
 * PageController test runner.
 */
class PageControllerTest {
    /**
     * Passed count.
     *
     * @var int
     */
    private int $passed = 0;

    /**
     * Failed count.
     *
     * @var int
     */
    private int $failed = 0;

    /**
     * Run all tests.
     *
     * @return int Exit code.
     */
    public function run(): int {
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

    /**
     * Record a pass.
     *
     * @param string $msg Message to display.
     *
     * @return void
     */
    private function pass(string $msg): void {
        printf("\033[0;32m[PASS]\033[0m %s\n", $msg);
        $this->passed++;
    }

    /**
     * Record a fail.
     *
     * @param string $msg Message to display.
     *
     * @return void
     */
    private function fail(string $msg): void {
        printf("\033[0;31m[FAIL]\033[0m %s\n", $msg);
        $this->failed++;
    }

    /**
     * Test home page.
     *
     * @return void
     */
    private function testHome(): void {
        ob_start();
        $res = PageController::handle(['/']);
        ob_get_clean();

        if ($res !== false && strpos($res, 'Inicio') !== false) {
            $this->pass("PageController::handle returns home document");
        } else {
            $this->fail("PageController::handle failed for home");
        }
    }

    /**
     * Test not found.
     *
     * @return void
     */
    private function testNotFound(): void {
        $res = PageController::handle(['/path-that-does-not-exist']);

        if ($res === false) {
            $this->pass("PageController::handle returns false for missing");
        } else {
            $this->fail("PageController::handle should return false");
        }
    }
}

$test = new PageControllerTest();
exit($test->run());
