# PHP Core Utilities

A collection of portable, zero-dependency PHP utilities following the Unix Philosophy.

## Philosophy

Each utility is:

- **Single-purpose**: Does one thing well
- **Self-contained**: No external dependencies (vendored only)
- **Portable**: Works on any PHP 8.0+ environment
- **Tested**: Hermetic test suite included
- **Documented**: README with usage examples

## Available Utilities

### Database & Models

- **[Db](Db/)** - Database wrapper (RedBean abstraction)
- **[Model](Db/)** - Generic DB entity with property bag pattern

### HTTP & Routing

- **[Http](Http/)** - HTTP utilities (headers, requests, responses)
- **[Route](Route/)** - Minimalist HTTP router

### String & Text

- **[Str](Str/)** - String manipulation utilities

### Configuration & State

- **[Conf](Conf/)** - Configuration container
- **[Cookie](Cookie/)** - Secure cookie handler

### Assets & Resources

- **[Css](Css/)** - CSS manipulation
- **[Js](Js/)** - JavaScript bundling
- **[Img](Img/)** - Image manipulation

### Filesystem

- **[Fs](Fs/)** - Filesystem utilities

### Templating

- **[Template](Template/)** - Custom template engine

## Testing

Each utility has its own test suite. Run all tests:

```bash
./test.sh
```

Or test individual utilities:

```bash
cd Conf && ./test.sh
cd Db && ./test.sh
cd Str && ./test.sh
```

### Auto-Discovery

The group test (`./test.sh`) **automatically discovers** all subdirectories (except `vnd/`) and runs their tests. This means:

- ✅ **No hardcoded lists** - tests auto-discover utilities
- ✅ **Self-maintaining** - add a new utility, tests run automatically
- ✅ **Always up-to-date** - no manual updates needed

**To add a new utility:**

1. Create folder: `NewUtility/`
2. Add: `NewUtility.php`, `README.md`, `test.sh`
3. Done! Group test finds it automatically

## Usage

All utilities are autoloaded via the root `autoload.php`:

```php
require_once __DIR__ . '/../../../../autoload.php';

// Now all utilities are available
Conf::set('app.name', 'My App');
$db = Db::init('sqlite:app.db');
$slug = Str::slug('Hello World');
```

## Standards

All utilities follow:

- **KCS PHP** - KaisarCode Software Standards for PHP
- **PSR-12** - PHP coding style (with KCS overrides)
- **Zero Dependencies** - Self-contained, portable code

## License

Released under the GNU General Public License v3.0.

---

**Author:** KaisarCode  
**Website:** <https://kaisarcode.com>  
**License:** GNU GPL v3.0

© 2025 KaisarCode
