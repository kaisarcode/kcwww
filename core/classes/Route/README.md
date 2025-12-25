# Route - Minimalist HTTP Router

Minimalistic HTTP router that matches request method and URI path, automatically halts on first matching route.

## Features

- GET/POST/PUT/DELETE/ALL methods
- Regex pattern matching
- Callback-based routing
- Password protection
- Error handling
- Auto JSON/string response

## Usage

```php
require_once __DIR__ . '/../../autoload.php';

// Simple routes
Route::get('/', function() {
    return 'Home Page';
});

Route::get('/users', function() {
    return ['users' => ['John', 'Jane']]; // Auto JSON
});

Route::post('/users', function() {
    $name = $_POST['name'];
    return ['success' => true, 'name' => $name];
});

// Pattern matching
Route::get('/users/(\d+)', function($matches) {
    $id = $matches[1];
    return "User ID: $id";
});

// Return false to continue to next route
Route::get('/admin/.*', function() {
    if (!isset($_SESSION['admin'])) {
        Http::set401();
        return true; // Stop here
    }
    return false; // Continue to next route
});

// Password protection
Route::protect('POST', '/admin/.*', 'secret-password');

// Error handler
Route::error(function($code) {
    echo "Error $code";
});
```

## API

### `Route::get(string $path, callable $callback): void`

Register GET route.

### `Route::post(string $path, callable $callback): void`

Register POST route.

### `Route::put(string $path, callable $callback): void`

Register PUT route.

### `Route::delete(string $path, callable $callback): void`

Register DELETE route.

### `Route::all(string $path, callable $callback): void`

Register route for any HTTP method.

### `Route::protect(string $method, string $path, string $password): void`

Protect route with password.

### `Route::error(callable $callback): void`

Register error handler for 404/500 errors.

### `Route::setAuthParam(string $name): void`

Set parameter name for authentication (default: 'routepassword').

## Callback Return Values

- `false` - Continue to next route
- `true` - Stop, no output
- `string|number` - Echo as response
- `array|object` - JSON encode and echo

## License

Released under the GNU General Public License v3.0.

---

**Author:** KaisarCode  
**Website:** <https://kaisarcode.com>  
**License:** GNU GPL v3.0

© 2025 KaisarCode
