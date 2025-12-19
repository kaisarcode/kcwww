# phproj - Modern PHP Project Baseline

**The ultimate minimalist, PWA-ready, standards-compliant PHP baseline for modern web applications.**

## Philosophy

`phproj` follows the **Unix Philosophy** (small, composable tools) and **KaisarCode Software Standards (KCS)** to provide a zero-dependency foundation that is portable, high-performance, and "engine-first".

### Core Principles

- **Zero External Dependencies**: All libraries (RedBeanPHP, Parsedown) are strictly vendored.
- **PWA-First**: Built-in support for Service Workers, Manifests, and dynamic icon generation.
- **Architectural Efficiency**: Strictly follows KCS for directory structure and naming.
- **Asset Sovereignty**: Native JS/CSS bundling and minification without Node.js.
- **Hermetic Testing**: Test suite validates standards compliance and functionality.

## Architecture

The project is organized in a flat structure for simplicity and clarity.

```text
phproj/
├── index.php           # Entry point
├── autoload.php        # Global PSR-4 autoloader
├── setup.php           # Environment initialization
├── conf.php            # Application configuration
├── routes.php          # Semantic route definitions
├── test.sh             # Master test runner
├── classes/            # Core utilities (Bundler, Http, Str, etc.)
├── controllers/        # Application logic (MVC)
├── models/             # Data persistence (RedBean wrappers)
├── views/              # Templates, CSS, JS, and raw assets
└── var/                # Runtime data (cache, logs, dev flags)
```

## Features

### Native Asset Bundling

The `Bundler` utility resolves ES module imports in JS and `@import` declarations in CSS recursively. It inlines dependencies into single, minified payloads served via `AssetsController`.

- **CSS**: Resolves variables and inlines imports.
- **JS**: Bundles modules and strips exports for browser compatibility.

### PWA Engine

Automatic generation of all PWA requirements:

- **Manifest**: Dynamic `manifest.json` based on `Conf` settings.
- **Service Worker**: Automatic `worker.js` with offline support.
- **Dynamic Icons**: Generates all required icon sizes (16x16 to 512x512) on-the-fly from a single SVG source.

### Dynamic Image Processing

Intelligent image delivery via `/img/{path}-{dims}.{format}`.

- Supports resizing (e.g., `-w800`, `-h600`, `-w400h400`).
- Format conversion on-the-fly (e.g., converting PNG to WebP).
- Built-in disk caching in `var/cache/img`.

### Auto-API

Models in `models/` are automatically exposed via a semantic REST API:

- `GET /api/user/1`: Fetches user with ID 1.
- `GET /api/user`: Lists users (with filtering and pagination).

## Core Utilities

`phproj` includes a suite of engine-room utilities (Http, Fs, Str, Db, etc.) designed for speed and portability. For detailed documentation on individual classes, see:
See: **[classes/README.md](classes/README.md)**

## Testing

Run the test suite to validate standards compliance:

```bash
./test.sh
```

## Standards

This project is the reference implementation for **KaisarCode Software Standards (KCS PHP)**:

- **Strict Typing**: Mandatory for all methods and properties.
- **Documentation**: 100% DocBlock coverage explaining *why* logic exists.
- **Formatting**: PSR-12 with KCS overrides (e.g., same-line braces).
- **Security**: Built-in XSS protection, CSRF readiness, and secure defaults.

---

**Author:** KaisarCode  
**Website:** [https://kaisarcode.com](https://kaisarcode.com)  
**License:** GNU GPL v3.0  
© 2025 KaisarCode
