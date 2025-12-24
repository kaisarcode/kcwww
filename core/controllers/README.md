# Core Controllers

This module provides the base controller architecture and core controllers for the application.

## Classes

- `Controller` - Abstract base class with shared utilities
- `AssetsController` - Handles CSS and JS bundling
- `ErrorController` - Global HTTP error handling
- `PwaController` - PWA manifest, worker, and icons
- `ImagesController` - Dynamic image processing

## Usage

Include `core/routes/core.php` in your application entry point to enable all core routes.
