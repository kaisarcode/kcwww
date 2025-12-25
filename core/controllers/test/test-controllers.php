<?php
/**
 * Test suite for Core Controllers
 */

$root = getenv('PROJECT_ROOT');
require_once $root . '/autoload.php';

autoload([
    $root . '/classes',
    $root . '/controllers',
    $root . '/models',
]);

class ControllerTest {
    private int $passed = 0;
    private int $failed = 0;

    public function run(): int {
        $this->testControllerExists();
        $this->testAssetsController();
        $this->testErrorController();
        $this->testPwaController();
        $this->testImagesController();

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

    private function testControllerExists(): void {
        if (class_exists('Controller')) {
            $this->pass('Controller base class exists');
        } else {
            $this->fail('Controller base class not found');
        }
    }

    private function testAssetsController(): void {
        if (class_exists('AssetsController')) {
            $this->pass('AssetsController class exists');
        } else {
            $this->fail('AssetsController class not found');
            return;
        }

        $parents = class_parents('AssetsController');
        if (in_array('Controller', $parents)) {
            $this->pass('AssetsController extends Controller');
        } else {
            $this->fail('AssetsController does not extend Controller');
        }

        if (method_exists('AssetsController', 'styles')) {
            $this->pass('AssetsController::styles exists');
        } else {
            $this->fail('AssetsController::styles missing');
        }

        if (method_exists('AssetsController', 'script')) {
            $this->pass('AssetsController::script exists');
        } else {
            $this->fail('AssetsController::script missing');
        }
    }

    private function testErrorController(): void {
        if (class_exists('ErrorController')) {
            $this->pass('ErrorController class exists');
        } else {
            $this->fail('ErrorController class not found');
            return;
        }

        $parents = class_parents('ErrorController');
        if (in_array('Controller', $parents)) {
            $this->pass('ErrorController extends Controller');
        } else {
            $this->fail('ErrorController does not extend Controller');
        }

        if (method_exists('ErrorController', 'handle')) {
            $this->pass('ErrorController::handle exists');
        } else {
            $this->fail('ErrorController::handle missing');
        }

        if (method_exists('ErrorController', 'register')) {
            $this->pass('ErrorController::register exists');
        } else {
            $this->fail('ErrorController::register missing');
        }
    }

    private function testPwaController(): void {
        if (class_exists('PwaController')) {
            $this->pass('PwaController class exists');
        } else {
            $this->fail('PwaController class not found');
            return;
        }

        $parents = class_parents('PwaController');
        if (in_array('Controller', $parents)) {
            $this->pass('PwaController extends Controller');
        } else {
            $this->fail('PwaController does not extend Controller');
        }

        if (method_exists('PwaController', 'manifest')) {
            $this->pass('PwaController::manifest exists');
        } else {
            $this->fail('PwaController::manifest missing');
        }

        if (method_exists('PwaController', 'worker')) {
            $this->pass('PwaController::worker exists');
        } else {
            $this->fail('PwaController::worker missing');
        }

        if (method_exists('PwaController', 'icon')) {
            $this->pass('PwaController::icon exists');
        } else {
            $this->fail('PwaController::icon missing');
        }
    }

    private function testImagesController(): void {
        if (class_exists('ImagesController')) {
            $this->pass('ImagesController class exists');
        } else {
            $this->fail('ImagesController class not found');
            return;
        }

        $parents = class_parents('ImagesController');
        if (in_array('Controller', $parents)) {
            $this->pass('ImagesController extends Controller');
        } else {
            $this->fail('ImagesController does not extend Controller');
        }

        if (method_exists('ImagesController', 'process')) {
            $this->pass('ImagesController::process exists');
        } else {
            $this->fail('ImagesController::process missing');
        }
    }

    public function summary(): void {
        echo "\n";
        echo "Total: " . ($this->passed + $this->failed) . " | ";
        echo "\033[0;32mPassed: {$this->passed}\033[0m | ";
        echo "\033[0;31mFailed: {$this->failed}\033[0m\n";
    }
}

$test = new ControllerTest();
exit($test->run());
