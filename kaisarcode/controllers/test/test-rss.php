<?php
/**
 * Test suite for RssController
 * Summary: Tests RssController RSS feed generation.
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
 * RssController test runner.
 */
class RssControllerTest {
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
        $this->testRssOutput();
        $this->testRssPagination();
        $this->testRssPath();

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
     * Test RSS output.
     *
     * @return void
     */
    private function testRssOutput(): void {
        $_GET = [];
        ob_start();
        RssController::handle();
        $output = ob_get_clean();

        $hasXml = strpos($output, '<?xml') !== false;
        $hasRss = strpos($output, '<rss') !== false;

        if ($hasXml && $hasRss) {
            $this->pass("RssController::handle returns valid XML");
        } else {
            $this->fail("RssController::handle returned invalid output");
        }

        if (strpos($output, '<title>KaisarCode</title>') !== false) {
            $this->pass("RssController output contains site title");
        } else {
            $this->fail("RssController output missing site title");
        }
    }

    /**
     * Test RSS pagination.
     *
     * @return void
     */
    private function testRssPagination(): void {
        $_GET['l'] = 1;
        ob_start();
        RssController::handle();
        $output = ob_get_clean();

        $hasLimit = strpos($output, '<documentAmount>1</documentAmount>');
        if ($hasLimit !== false) {
            $this->pass("RssController respects limit parameter 'l'");
        } else {
            $this->fail("RssController ignored limit parameter 'l'");
        }

        $_GET['p'] = 2;
        ob_start();
        RssController::handle();
        $output = ob_get_clean();

        $hasPage = strpos($output, '<currentPage>2</currentPage>');
        if ($hasPage !== false) {
            $this->pass("RssController respects page parameter 'p'");
        } else {
            $this->fail("RssController ignored page parameter 'p'");
        }
    }

    /**
     * Test RSS path.
     *
     * @return void
     */
    private function testRssPath(): void {
        $_GET = ['path' => '/non-existent'];
        ob_start();
        RssController::handle();
        $output = ob_get_clean();

        $hasZero = strpos($output, '<totalDocuments>0</totalDocuments>');
        if ($hasZero !== false) {
            $this->pass("RssController handles non-existent path");
        } else {
            $this->fail("RssController failed to handle empty path");
        }
    }
}

$test = new RssControllerTest();
exit($test->run());
