#!/bin/sh
#
# Test harness for Route utility
#

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
KCVAL="${KCVAL:-kcval}"

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
    info "kcval not found, skipping validation"
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

printf "\n%bAll tests passed%b\n" "${GREEN}" "${NC}"

