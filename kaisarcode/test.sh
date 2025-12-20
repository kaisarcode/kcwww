#!/bin/sh
#
# kaisarcode - Test Suite
# Validates site-specific functionality and model operations
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

header "kaisarcode Test Suite"

# Validation
header "Validation"
if command -v "$KCVAL" >/dev/null; then
    "$KCVAL" "$SCRIPT_DIR/test.sh" >/dev/null 2>&1 || fail "test.sh failed validation"
    pass "KCS validation passed"
else
    info "kcval not found, skipping validation"
fi

# Test DocModel
header "Testing DocModel"

php "$SCRIPT_DIR/test/test_doc_model.php"
TEST_RESULT=$?

if [ $TEST_RESULT -ne 0 ]; then
    fail "DocModel tests failed"
fi

pass "DocModel tests passed"

# Test API endpoint
header "Testing API Endpoint"

php "$SCRIPT_DIR/test/test_api.php"
API_RESULT=$?

if [ $API_RESULT -ne 0 ]; then
    fail "API tests failed"
fi

pass "API tests passed"

# Summary
pass "All kaisarcode tests passed"
exit 0
