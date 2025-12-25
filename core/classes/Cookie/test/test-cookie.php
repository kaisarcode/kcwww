<?php
/**
 * Test suite for Cookie utility
 */

require_once __DIR__ . '/../Cookie.php';

// Mocking setcookie for CLI environment if needed, or rely on PHP behavior
// In CLI, setcookie doesn't do much but might warn.
// We can override it in the namespace if needed, but let's see if we can just suppress warnings.

class CookieTest {
    private int $passed = 0;
    private int $failed = 0;

    public function run(): int {
        $this->testSetGet();
        $this->testDelete();
        $this->testEncryption();

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

    private function testSetGet(): void {
        // Suppress "Cannot modify header information" warning in CLI
        @Cookie::set('test_cookie', 'test_value');

        if (isset($_COOKIE['test_cookie']) && $_COOKIE['test_cookie'] === 'test_value') {
            $this->pass('Cookie::set updates $_COOKIE');
        } else {
            $this->fail('Cookie::set did not update $_COOKIE');
        }

        $val = Cookie::get('test_cookie');
        if ($val === 'test_value') {
            $this->pass('Cookie::get retrieves value');
        } else {
            $this->fail('Cookie::get failed to retrieve value');
        }

        if (Cookie::has('test_cookie')) {
            $this->pass('Cookie::has returns true');
        } else {
            $this->fail('Cookie::has returns false');
        }
    }

    private function testDelete(): void {
        @Cookie::set('del_cookie', 'del_val');
        @Cookie::delete('del_cookie');

        if (!isset($_COOKIE['del_cookie']) && !Cookie::has('del_cookie')) {
            $this->pass('Cookie::delete removes cookie');
        } else {
            $this->fail('Cookie::delete failed to remove cookie');
        }
    }

    private function testEncryption(): void {
        $key = 'secret_key_123';
        $original = 'sensitive_data';

        @Cookie::set('enc_cookie', $original, 3600, $key);

        $raw = $_COOKIE['enc_cookie'];

        if ($raw !== $original) {
            $this->pass('Encrypted cookie is stored encrypted');
        } else {
            $this->fail('Encrypted cookie stored as plain text');
        }

        $decrypted = Cookie::get('enc_cookie', $key);
        if ($decrypted === $original) {
            $this->pass('Cookie::get decrypts correctly');
        } else {
            $this->fail('Cookie::get failed to decrypt');
        }

        // Test with wrong key
        $wrong = Cookie::get('enc_cookie', 'wrong_key');
        if ($wrong === false || $wrong === '') { // decrypt returns empty string on fail
            $this->pass('Cookie::get with wrong key returns false/empty');
        } else {
            $this->fail('Cookie::get with wrong key returned data');
        }
    }

    public function summary(): void {
        echo "\n";
        echo "Total: " . ($this->passed + $this->failed) . " | ";
        echo "\033[0;32mPassed: {$this->passed}\033[0m | ";
        echo "\033[0;31mFailed: {$this->failed}\033[0m\n";
    }
}

$test = new CookieTest();
exit($test->run());
