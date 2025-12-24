# Str - String Manipulation Utilities

Utility functions for string manipulation including sanitization, truncation, and slug generation.

## Features

- XSS sanitization
- Truncation with ellipsis
- Minification
- Random string generation
- Slug generation
- Normalization (remove accents)
- Custom trim functions

## Usage

```php
require_once __DIR__ . '/../../autoload.php';

// Sanitize user input
$safe = Str::sanitize('<script>alert("xss")</script>');

// Result: &lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;

// Truncate long text
$short = Str::truncate('This is a very long text', 10);

// Result: "This is a ..."

// Generate URL-safe slug
$slug = Str::slug('Hello World! 123');

// Result: "hello-world-123"

// Generate random string
$token = Str::random(32);

// Result: "aB3xY9..."

// Minify string
$min = Str::min("Hello    World\n\n  Test");

// Result: "Hello World Test"

// Remove comments
$clean = Str::rmc('<!-- comment --> code // comment');

// Result: " code "

// Normalize string
$norm = Str::normalize('Héllo Wörld');

// Result: "Hello World"

// Custom trim
$trimmed = Str::trim('  text  ', ' ');

// Result: "text"
```

## API

### `Str::sanitize(string $str = ''): string`

Sanitize string to prevent XSS attacks.

### `Str::truncate(string $str, int $len = 30, string $ellipsis = '...'): string`

Truncate string with optional ellipsis.

### `Str::slug(string $str): string`

Generate URL-safe slug from string.

### `Str::random(int $length, string $chars = '...'): string`

Generate secure random string.

### `Str::min(string $str = ''): string`

Minify string by collapsing whitespace.

### `Str::rmc(string $str = ''): string`

Remove HTML and JS comments from string.

### `Str::normalize(string $str): string`

Normalize string by removing accents and diacritics.

### `Str::ltrim(string $str = '', string $c = ' '): string`

Remove prefix from beginning of string.

### `Str::rtrim(string $str = '', string $c = ' '): string`

Remove suffix from end of string.

### `Str::trim(string $str = '', string $c = ' '): string`

Remove matching prefix and suffix from string.

### `Str::keepPre(string $str = ''): string`

Escape formatting inside `<pre>` blocks.

### `Str::restorePre(string $str = ''): string`

Restore formatting inside `<pre>` blocks.

## License

Released under the GNU General Public License v3.0.

---

**Author:** KaisarCode  
**Website:** <https://kaisarcode.com>  
**License:** GNU GPL v3.0

© 2025 KaisarCode
