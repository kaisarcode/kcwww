<?php
/**
 * Test suite for ApiController
 */

$root = getenv('PROJECT_ROOT');
require_once $root . '/autoload.php';

autoload([
    $root . '/classes',
    $root . '/controllers',
    $root . '/models',
]);

class ApiTest
{
    private int $passed = 0;
    private int $failed = 0;

    public function run(): int
    {
        // Mock request environment
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $this->testListDummy();
        $this->testCreateDummy();
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

    private function testListDummy(): void
    {
        // Add a record first so we have data
        DummyModel::create(['name' => 'API List Test'])->save();

        ob_start();
        ApiController::handle('dummy');
        $output = ob_get_clean();

        if (strpos($output, 'API List Test') !== false) {
            $this->pass("ApiController::handle('dummy') lists records");
        } else {
            $this->fail("ApiController::handle('dummy') failed to list records");
        }
    }

    private function testCreateDummy(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        // Mock POST data via our Http helper (it reads php://input if not in $_POST)
        // For testing we'll just put it in $_POST
        $_POST['name'] = 'API Create Test';

        ob_start();
        ApiController::handle('dummy');
        $output = ob_get_clean();

        if (strpos($output, 'API Create Test') !== false) {
            $this->pass("ApiController::handle('dummy') creates record via POST");
        } else {
            $this->fail("ApiController::handle('dummy') failed to create record");
        }

        // Clean up
        unset($_POST['name']);
    }

    private function testNotFound(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        ob_start();
        ApiController::handle('non_existent_model');
        $output = ob_get_clean();

        if (strpos($output, 'Model not found') !== false) {
            $this->pass("ApiController::handle handle missing model correctly");
        } else {
            $this->fail("ApiController::handle failed to notify missing model");
        }
    }
}

$test = new ApiTest();
exit($test->run());
