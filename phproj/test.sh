#!/bin/sh
#
# phproj - Master Test Runner
# Validates standards compliance and runs subdirectory tests
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

header "phproj Test Suite"

# Validation
header "Validation"
if command -v "$KCVAL" >/dev/null; then
    "$KCVAL" "$SCRIPT_DIR/README.md" >/dev/null 2>&1 || fail "README.md failed validation"
    "$KCVAL" "$SCRIPT_DIR/test.sh" >/dev/null 2>&1 || fail "test.sh failed validation"
    pass "KCS validation passed"
else
    info "kcval not found, skipping validation"
fi

TOTAL=0
PASSED=0
FAILED=0

# Find all test.sh files in subdirectories, excluding vnd
for test_file in $(find "$SCRIPT_DIR/classes" "$SCRIPT_DIR/controllers" -name "test.sh" ! -path "*/vnd/*" 2>/dev/null | sort); do
    rel_path="${test_file#"$SCRIPT_DIR"/}"
    dir_name=$(dirname "$rel_path" | sed 's|classes/||')

    TOTAL=$((TOTAL + 1))

    header "Testing $dir_name"

    if "$test_file"; then
        PASSED=$((PASSED + 1))
    else
        FAILED=$((FAILED + 1))
        printf "${RED}[FAIL]${NC} %s tests failed\n" "$dir_name"
    fi

    echo ""
done

# Summary
if [ "$TOTAL" -gt 0 ]; then
    info "Total: $TOTAL | Passed: $PASSED | Failed: $FAILED"
fi

if [ "$FAILED" -gt 0 ]; then
    fail "Some tests failed"
fi

pass "All tests passed"
exit 0
