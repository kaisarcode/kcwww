<?php
/**
 * Test suite for Http utility
 */

require_once __DIR__.'/../Http.php';

class HttpTest
{
    private int $passed = 0;
    private int $failed = 0;

    public function run(): int
    {
        $this->testCleanPath();
        $this->testMockedRequests();
        $this->testUserAgent();

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

    private function testCleanPath(): void
    {
        $tests = [
            '/foo//bar' => '/foo/bar',
            '//foo////bar//' => '/foo/bar/',
            'http://example.com//foo' => 'http://example.com/foo',
            'https://site.com//' => 'https://site.com/'
        ];

        foreach ($tests as $input => $expected) {
            $res = Http::cleanPath($input);
            if ($res === $expected) {
                $this->pass("Http::cleanPath('$input') -> '$expected'");
            } else {
                $this->fail("Http::cleanPath('$input') failed. Got '$res'");
            }
        }
    }

    private function testMockedRequests(): void
    {
        // Mock Globals for CLI
        $_GET['foo'] = 'bar';
        $_POST['baz'] = 'qux';
        $_COOKIE['token'] = '123';

        // Test getHttpVar
        if (Http::getHttpVar('foo') === 'bar')
            $this->pass('Http::getHttpVar reads GET');
        else
            $this->fail('Http::getHttpVar failed GET');

        if (Http::getHttpVar('baz') === 'qux')
            $this->pass('Http::getHttpVar reads POST');
        else
            $this->fail('Http::getHttpVar failed POST');

        if (Http::getHttpVar('token') === '123')
            $this->pass('Http::getHttpVar reads COOKIE');
        else
            $this->fail('Http::getHttpVar failed COOKIE');

        // Test issetHttpVar
        if (Http::issetHttpVar('foo'))
            $this->pass('Http::issetHttpVar returns true');
        else
            $this->fail('Http::issetHttpVar failed');

        if (!Http::issetHttpVar('nothing'))
            $this->pass('Http::issetHttpVar(missing) returns false');
        else
            $this->fail('Http::issetHttpVar(missing) failed');

        // Test getQueryString override
        // Current: foo=bar, baz=qux (POST not in GET params), token...
        // $_GET only has 'foo'
        $qs = Http::getQueryString(['new' => 'val']);
        // expect foo=bar&new=val
        parse_str(htmlspecialchars_decode($qs), $parsed);
        if (($parsed['foo'] ?? '') === 'bar' && ($parsed['new'] ?? '') === 'val') {
            $this->pass('Http::getQueryString works with override');
        } else {
            $this->fail("Http::getQueryString failed. Got '$qs'");
        }
    }

    private function testUserAgent(): void
    {
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X)';
        if (Http::isMobile())
            $this->pass('Http::isMobile detects iPhone');
        else
            $this->fail('Http::isMobile failed detect iPhone');

        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/90.0';
        if (!Http::isMobile())
            $this->pass('Http::isMobile rejects Desktop');
        else
            $this->fail('Http::isMobile failed reject Desktop');

        if (Http::isStandardBrowser())
            $this->pass('Http::isStandardBrowser detects Chrome');
        else
            $this->fail('Http::isStandardBrowser failed detect Chrome');
    }

    public function summary(): void
    {
        echo "\n";
        echo "Total: " . ($this->passed + $this->failed) . " | ";
        echo "\033[0;32mPassed: {$this->passed}\033[0m | ";
        echo "\033[0;31mFailed: {$this->failed}\033[0m\n";
    }
}

$test = new HttpTest();
exit($test->run());
