#!/bin/sh
#
# Test harness for Cookie utility
#

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
KCVAL="${KCVAL:-kc-val}"

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m'

# Helper functions
header() { printf "${CYAN}[TEST]${NC} %s\n" "$1"; }
pass() { printf "${GREEN}[PASS]${NC} %s\n" "$1"; }
fail() { printf "${RED}[FAIL]${NC} %s\n" "$1"; exit 1; }
info() { printf "${YELLOW}[INFO]${NC} %s\n" "$1"; }

# Validation
header "Validation"
if command -v "$KCVAL" >/dev/null; then
    "$KCVAL" "$SCRIPT_DIR/README.md" >/dev/null 2>&1 || fail "README.md failed validation"
    "$KCVAL" "$SCRIPT_DIR/test.sh" >/dev/null 2>&1 || fail "test.sh failed validation"
    pass "KCS validation passed"
else
    info "kc-val not found, skipping validation"
fi

# Functional tests
header "Cookie Tests"

if php "$SCRIPT_DIR/test/test-cookie.php"; then
    pass "All Cookie tests passed"
else
    fail "Cookie tests failed"
fi

printf "\n%bAll tests passed%b\n" "${GREEN}" "${NC}"

exit 0
