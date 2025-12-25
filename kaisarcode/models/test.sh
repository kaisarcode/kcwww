#!/bin/sh
#
# Test harness for KaisarCode Models
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
if command -v "$KCVAL" > /dev/null; then
    "$KCVAL" "$SCRIPT_DIR/test.sh" > /dev/null 2>&1 || fail "test.sh failed validation"
    pass "KCS validation passed"
else
    info "kc-val not found, skipping validation"
fi

# Functional tests
header "Model Tests"

# Export paths for tests
PROJECT_ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"
CORE_ROOT="$(cd "$PROJECT_ROOT/../core" && pwd)"
export PROJECT_ROOT
export CORE_ROOT

# Run all test-*.php files in test/ directory
for testfile in "$SCRIPT_DIR/test"/test-*.php; do
    [ -f "$testfile" ] || continue
    testname="$(basename "$testfile" .php)"
    if php "$testfile"; then
        pass "$testname passed"
    else
        fail "$testname failed"
    fi
done

printf "\n%bAll tests passed%b\n" "${GREEN}" "${NC}"
