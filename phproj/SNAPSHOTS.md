# Project Snapshots Log

This file tracks manual snapshots (git branches) created to serve as fallback points.

## snapshot/001

**Date:** 2025-12-17 20:31
**SHA:** d355ac3
**Description:** Baseline functional system. All Core Utils (Bundler, valid tests) implemented. Nixi exported. App entry point created.

## snapshot/002

**Date:** 2025-12-17 23:22
**SHA:** 97e4cc5
**Description:** Major refactor - Simplified structure (src/core, src/app removed), Controller architecture with inheritance, simplified test runner, autonomous core classes (Db removed), .kcgen folder naming.

## snapshot/003

**Date:** 2025-12-18 01:05
**SHA:** dbb28f6 (nixi) / 92838b2 (phproj)
**Description:** Nixi Security Refactor. Implemented "HTTPS is the new HTTP" philosophy. Automatic port allocation (81+ for HTTPS, 51230+ for HTTP redirects). Enforced HSTS, HTTP/2, and path isolation via --allow "index.php,pub". Export commands are now fully portable (no hardcoded ports).

## snapshot/004

**Date:** 2025-12-18 21:50
**SHA:** d7d508c
**Branch:** snapshot/004
**Description:** Resume project state. Synced master and slave to this point.
