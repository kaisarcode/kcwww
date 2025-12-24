# Conf - Configuration Container

Provides associative and nested key support for configuration management.

## Features

- Dot notation support (`app.db.host`)
- Nested arrays
- Hide sensitive values
- Get/Set/Delete operations

## Usage

```php
require_once __DIR__ . '/../../autoload.php';

// Set configuration
Conf::set('app.db.host', 'localhost');
Conf::set('app.db.port', 3306);
Conf::set('app.db.pass', 'secret', true); // hidden

// Get configuration
$host = Conf::get('app.db.host');
$port = Conf::get('app.db.port', 3306); // with default

// Get all excluding hidden
$config = Conf::all();

// Get all including hidden
$allConfig = Conf::all(true);

// Delete configuration
Conf::del('app.db.port');
```

## API

### `Conf::set(string|array $key, mixed $value = null, int|bool $hide = false): void`

Set a single key or multiple values.

### `Conf::get(string $key, mixed $default = null): mixed`

Retrieve a value by key, supporting dot notation.

### `Conf::del(string $key): void`

Remove a value by key, supporting dot notation.

### `Conf::exc(string|array $paths): void`

Mark paths as excluded from `all()`.

### `Conf::all(int|bool $hidden = false): array`

Get the entire config array.

## License

Released under the GNU General Public License v3.0.

---

**Author:** KaisarCode  
**Website:** <https://kaisarcode.com>  
**License:** GNU GPL v3.0

© 2025 KaisarCode
