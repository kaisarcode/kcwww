#!/bin/bash
#
# KaisarCode - Master Test Runner
# Validates standards compliance and runs subdirectory tests
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

header "KaisarCode Test Suite"

# Validation
header "Validation"
if command -v "$KCVAL" >/dev/null; then
    VALIDATION_FAILED=0

    # Validate README.md
    if [ -f "$SCRIPT_DIR/README.md" ]; then
        "$KCVAL" "$SCRIPT_DIR/README.md" >/dev/null 2>&1 || {
            printf '%s[FAIL]%s README.md\n' "$RED" "$NC"
            VALIDATION_FAILED=1
        }
    fi

    # Validate Shell scripts
    while IFS= read -r -d '' f; do
        "$KCVAL" "$f" >/dev/null 2>&1 || {
            printf '%s[FAIL]%s %s\n' "$RED" "$NC" "${f#"$SCRIPT_DIR"/}"
            VALIDATION_FAILED=1
        }
    done < <(find "$SCRIPT_DIR" -name "*.sh" ! -path "*/var/*" -print0 2>/dev/null)

    # Validate PHP files
    while IFS= read -r -d '' f; do
        "$KCVAL" "$f" >/dev/null 2>&1 || {
            printf '%s[FAIL]%s %s\n' "$RED" "$NC" "${f#"$SCRIPT_DIR"/}"
            VALIDATION_FAILED=1
        }
    done < <(find "$SCRIPT_DIR" -name "*.php" ! -path "*/var/*" ! -path "*/test/*" -print0 2>/dev/null)

    # Validate CSS files
    while IFS= read -r -d '' f; do
        "$KCVAL" "$f" >/dev/null 2>&1 || {
            printf '%s[FAIL]%s %s\n' "$RED" "$NC" "${f#"$SCRIPT_DIR"/}"
            VALIDATION_FAILED=1
        }
    done < <(find "$SCRIPT_DIR" -name "*.css" ! -path "*/var/*" -print0 2>/dev/null)

    # Validate Markdown files
    while IFS= read -r -d '' f; do
        "$KCVAL" "$f" >/dev/null 2>&1 || {
            printf '%s[FAIL]%s %s\n' "$RED" "$NC" "${f#"$SCRIPT_DIR"/}"
            VALIDATION_FAILED=1
        }
    done < <(find "$SCRIPT_DIR" -name "*.md" ! -path "*/var/*" ! -name "README.md" -print0 2>/dev/null)

    if [ "$VALIDATION_FAILED" -eq 1 ]; then
        fail "KCS validation failed"
    fi
    pass "KCS validation passed"
else
    info "kc-val not found, skipping validation"
fi

TOTAL=0
PASSED=0
FAILED=0

# Find all test.sh files in subdirectories
while IFS= read -r test_file; do
    rel_path="${test_file#"$SCRIPT_DIR"/}"
    dir_name=$(dirname "$rel_path")

    TOTAL=$((TOTAL + 1))

    header "Testing $dir_name"

    if "$test_file"; then
        PASSED=$((PASSED + 1))
    else
        FAILED=$((FAILED + 1))
        printf '%s[FAIL]%s %s tests failed\n' "$RED" "$NC" "$dir_name"
    fi

    echo ""
done < <(find "$SCRIPT_DIR/models" "$SCRIPT_DIR/controllers" -name "test.sh" 2>/dev/null | sort)

# Summary
if [ "$TOTAL" -gt 0 ]; then
    info "Total: $TOTAL | Passed: $PASSED | Failed: $FAILED"
fi

if [ "$FAILED" -gt 0 ]; then
    fail "Some tests failed"
fi

pass "All tests passed"
exit 0
