#!/bin/sh
#
# CSS Validation Test Suite
# Runs KCS validation on CSS files
#

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
KCVAL="${KCVAL:-kc-val}"

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m'

header() { printf '%s[TEST]%s %s\n' "$CYAN" "$NC" "$1"; }
pass() { printf '%s[PASS]%s %s\n' "$GREEN" "$NC" "$1"; }
fail() { printf '%s[FAIL]%s %s\n' "$RED" "$NC" "$1"; exit 1; }
info() { printf '%s[INFO]%s %s\n' "$YELLOW" "$NC" "$1"; }

header "CSS Validation"

if command -v "$KCVAL" >/dev/null; then

    # Run kcval on all CSS files in current directory
    if find "$SCRIPT_DIR" -name "*.css" -print0 | xargs -0 -r "$KCVAL"; then
        pass "CSS validation passed"
    else
        fail "CSS validation failed"
    fi
else
    info "kc-val not found, skipping validation"
fi

exit 0
