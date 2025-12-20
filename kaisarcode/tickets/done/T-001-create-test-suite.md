---
status: CLOSED
priority: P2
created: 2025-12-20
closed: 2025-12-20
---

# T-001: Create Test Suite for KaisarCode Site

## Description

The `phproj` core has tests for models and controllers. The `kaisarcode` child site needs its own test suite to validate site-specific functionality.

## Requirements

1. Create `test.sh` in `/home/kaisar/www/kaisarcode/` following the same pattern as `phproj/test.sh`
2. Add tests for:
   - `DocModel` instantiation and CRUD operations
   - Site-specific configuration loading (`conf.php`)
   - Layout inheritance (verify `layout.html` extends core correctly)
   - API endpoint `/api/doc` responses

## Acceptance Criteria

- [ ] `kaisarcode/test.sh` exists and is executable
- [ ] Tests validate `DocModel` create, read, update, delete
- [ ] Tests validate API responses for `/api/doc`
- [ ] Tests pass without errors
- [ ] Code passes `kcval` validation

## Notes

Reference `phproj/test.sh` for the expected test structure and patterns.
