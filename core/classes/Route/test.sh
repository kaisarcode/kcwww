#!/bin/sh
#
# Test harness for Route utility
#

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
KCVAL="${KCVAL:-kc-val}"

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m'

header() { printf "${CYAN}[TEST]${NC} %s\n" "$1"; }
pass() { printf "${GREEN}[PASS]${NC} %s\n" "$1"; }
fail() { printf "${RED}[FAIL]${NC} %s\n" "$1"; exit 1; }
info() { printf "${YELLOW}[INFO]${NC} %s\n" "$1"; }

# Validation
header "Validation"
if command -v "$KCVAL" > /dev/null; then
    "$KCVAL" "$SCRIPT_DIR/README.md" > /dev/null 2>&1 || fail "README.md failed validation"
    "$KCVAL" "$SCRIPT_DIR/test.sh" > /dev/null 2>&1 || fail "test.sh failed validation"
    pass "KCS validation passed"
else
    info "kc-val not found, skipping validation"
fi

# Functional tests
header "Route Tests"

# Test GET route
# shellcheck disable=SC2016
output=$(php -r '
$_SERVER["REQUEST_METHOD"] = "GET";
$_SERVER["REQUEST_URI"] = "/test";
require_once "'"$SCRIPT_DIR"'/Route.php";
Route::get("/test", function() { return "OK"; });
')

if [ "$output" != "OK" ]; then
    fail "GET route failed"
fi
pass "GET route works"

# Test pattern matching
# shellcheck disable=SC2016
output=$(php -r '
$_SERVER["REQUEST_METHOD"] = "GET";
$_SERVER["REQUEST_URI"] = "/users/123";
require_once "'"$SCRIPT_DIR"'/Route.php";
Route::get("/users/(\d+)", function($m) { return $m[1]; });
')

if [ "$output" != "123" ]; then
    fail "Pattern matching failed"
fi
pass "Pattern matching works"

# Test JSON response
# shellcheck disable=SC2016
output=$(php -r '
$_SERVER["REQUEST_METHOD"] = "GET";
$_SERVER["REQUEST_URI"] = "/json";
require_once "'"$SCRIPT_DIR"'/Route.php";
Route::get("/json", function() { return ["status" => "ok"]; });
')

if [ "$output" != '{"status":"ok"}' ]; then
    fail "JSON response failed"
fi
pass "JSON response works"

# Test protect with default routepassword
# shellcheck disable=SC2016
output=$(php -r '
$_SERVER["REQUEST_METHOD"] = "GET";
$_SERVER["REQUEST_URI"] = "/secret";
$_GET["routepassword"] = "correct";
require_once "'"$SCRIPT_DIR"'/Route.php";
Route::protect("GET", "/secret", "correct");
Route::get("/secret", function() { return "SECRET_ACCESS"; });
')

if [ "$output" != "SECRET_ACCESS" ]; then
    fail "Protect failed with correct routepassword"
fi
pass "Protect works with default routepassword"

# Test protect manually changing authParam to 'token'
# shellcheck disable=SC2016
output=$(php -r '
$_SERVER["REQUEST_METHOD"] = "GET";
$_SERVER["REQUEST_URI"] = "/token-secret";
$_GET["token"] = "valid-token";
require_once "'"$SCRIPT_DIR"'/Route.php";
Route::setAuthParam("token");
Route::protect("GET", "/token-secret", "valid-token");
Route::get("/token-secret", function() { return "TOKEN_ACCESS"; });
')

if [ "$output" != "TOKEN_ACCESS" ]; then
    fail "Protect failed after changing authParam to token via setter"
fi
pass "Protect works after changing authParam to token via setter"

# Test protect with WRONG password
# shellcheck disable=SC2016
output=$(php -r '
$_SERVER["REQUEST_METHOD"] = "GET";
$_SERVER["REQUEST_URI"] = "/secret";
$_GET["routepassword"] = "wrong";
require_once "'"$SCRIPT_DIR"'/Route.php";
Route::protect("GET", "/secret", "correct");
Route::get("/secret", function() { return "SECRET_ACCESS"; });
')

if [ "$output" = "SECRET_ACCESS" ]; then
    fail "Protect FAILED to block wrong password"
fi
pass "Protect blocks wrong password"

# Test protect with Authorization Bearer header
# shellcheck disable=SC2016
output=$(php -r '
$_SERVER["REQUEST_METHOD"] = "GET";
$_SERVER["REQUEST_URI"] = "/secret";
$_SERVER["HTTP_AUTHORIZATION"] = "Bearer correct-token";
require_once "'"$SCRIPT_DIR"'/Route.php";
Route::protect("GET", "/secret", "correct-token");
Route::get("/secret", function() { return "BEARER_ACCESS"; });
')

if [ "$output" != "BEARER_ACCESS" ]; then
    fail "Protect failed with Authorization Bearer header"
fi
pass "Protect works with Authorization Bearer header"

# Test protect with Session
# shellcheck disable=SC2016
output=$(php -r '
$_SERVER["REQUEST_METHOD"] = "GET";
$_SERVER["REQUEST_URI"] = "/secret";
session_start();
$_SESSION["routepassword"] = "session-pass";
require_once "'"$SCRIPT_DIR"'/Route.php";
Route::protect("GET", "/secret", "session-pass");
Route::get("/secret", function() { return "SESSION_ACCESS"; });
')

if [ "$output" != "SESSION_ACCESS" ]; then
    fail "Protect failed with Session password"
fi
pass "Protect works with Session password"

printf "\n%bAll tests passed%b\n" "${GREEN}" "${NC}"

