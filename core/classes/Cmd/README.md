# Cmd - Secure Command Execution Adapter

Provides safe access to system commands (kcap/kcai apps) through whitelisting and argument validation.

## Features

- **Whitelist-based**: Only registered commands can be executed
- **Argument validation**: Optional whitelist for allowed arguments
- **Command injection prevention**: All arguments are escaped
- **Output capture**: Capture stdout or stream directly
- **Exit code handling**: Check command success/failure
- **Secure by default**: No arbitrary command execution

## Security Model

- **Explicit Registration**: Commands must be registered before use
- **Path Validation**: Verifies executable exists and is executable
- **Argument Whitelisting**: Optionally restrict allowed arguments
- **Shell Escaping**: All arguments are properly escaped
- **No Interpolation**: Commands cannot be modified after registration

## Usage

### Basic Usage

```php
require_once __DIR__ . '/../../../../../../autoload.php';

// Register a command
Cmd::register('nixi', '/home/kaisar/bin/kaisarcode/kcap/nixi/nixi');

// Execute command
$result = Cmd::exec('nixi', ['--version']);
echo $result['output'];      // Command output
echo $result['exit_code'];   // Exit code (0 = success)

// Simple execution (output only)
$version = Cmd::run('nixi', ['--version']);
echo $version;

// Test if command succeeds
if (Cmd::test('nixi', ['--help'])) {
    echo "Command succeeded";
}
```

### With Argument Whitelisting

```php
// Register with allowed arguments
Cmd::register('kcai-search', '/home/kaisar/bin/kaisarcode/kcai/core/kcai-search/kcai-search', [
    '--help',
    '--version',
    '--query'
]);

// This works
Cmd::run('kcai-search', ['--help']);

// This throws exception (not in whitelist)
Cmd::run('kcai-search', ['--dangerous-flag']); // RuntimeException
```

### Multiple Commands

```php
// Register multiple kcai apps
Cmd::register('nixi', '/home/kaisar/bin/kaisarcode/kcap/nixi/nixi');
Cmd::register('kcai-search', '/home/kaisar/bin/kaisarcode/kcai/core/kcai-search/kcai-search');
Cmd::register('kcai-gen', '/home/kaisar/bin/kaisarcode/kcai/core/kcai-gen/kcai-gen');

// Check what's available
$commands = Cmd::getWhitelist();
print_r($commands); // ['nixi', 'kcai-search', 'kcai-gen']

// Check if specific command is whitelisted
if (Cmd::isWhitelisted('nixi')) {
    echo "nixi is available";
}
```

### Stream Output (No Capture)

```php
// Stream output directly (useful for long-running commands)
$result = Cmd::exec('nixi', ['status'], false);
// Output streams to browser/terminal
echo "Exit code: " . $result['exit_code'];
```

## API

### `Cmd::register(string $name, string $path, array $allowedArgs = []): void`

Register a command in the whitelist.

**Parameters:**

- `$name` - Command alias (e.g., 'nixi', 'kcai-search')
- `$path` - Full path to executable
- `$allowedArgs` - Optional array of allowed arguments

**Throws:** `RuntimeException` if command not found or not executable

### `Cmd::exec(string $name, array $args = [], bool $captureOutput = true): array`

Execute a whitelisted command.

**Returns:** `['output' => string, 'exit_code' => int]`

**Throws:** `RuntimeException` if command not whitelisted or args invalid

### `Cmd::run(string $name, array $args = []): string`

Execute command and return only output.

### `Cmd::test(string $name, array $args = []): bool`

Execute command and check if successful (exit code 0).

### `Cmd::isWhitelisted(string $name): bool`

Check if a command is whitelisted.

### `Cmd::getWhitelist(): array`

Get list of whitelisted command names.

### `Cmd::clearWhitelist(): void`

Clear all registered commands.

## Security Best Practices

- **Minimal Whitelist**: Only register commands you actually need
- **Argument Validation**: Use argument whitelist when possible
- **Least Privilege**: Run PHP process with minimal permissions
- **Path Verification**: Always use absolute paths
- **Input Validation**: Validate user input before passing to commands

## Example: Web Interface for kcai-search

```php
<?php
require_once 'autoload.php';

// Register kcai-search
Cmd::register('kcai-search', '/home/kaisar/bin/kaisarcode/kcai/core/kcai-search/kcai-search');

// Get search query from user
$query = $_GET['q'] ?? '';

if ($query) {
    // Safe execution - arguments are escaped
    $results = Cmd::run('kcai-search', ['--query', $query]);
    echo "<pre>$results</pre>";
}
?>
```

## License

Released under the GNU General Public License v3.0.

---

**Author:** KaisarCode  
**Website:** <https://kaisarcode.com>  
**License:** GNU GPL v3.0

© 2025 KaisarCode
