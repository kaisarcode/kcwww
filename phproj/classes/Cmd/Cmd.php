<?php
/**
 * Cmd - Secure command execution adapter
 * Summary: Executes whitelisted system commands with argument validation
 *
 * Author:  KaisarCode
 * Website: https://kaisarcode.com
 * License: GNU GPL v3.0
 * License URL: https://www.gnu.org/licenses/gpl-3.0.html
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License.
 */

/**
 * Secure command execution adapter with whitelisting.
 * 
 * Provides safe access to system commands (kcap/kcai apps) by:
 * - Whitelisting allowed commands
 * - Validating arguments
 * - Preventing command injection
 * - Capturing output and exit codes
 */
class Cmd
{

    /**
     * Whitelist of allowed commands
     * 
     * @var array<string, array{path: string, args?: array<string>}>
     */
    private static array $whitelist = [];

    /**
     * Register a command in the whitelist
     *
     * @param string $name Command alias (e.g., 'nixi', 'kcai-search')
     * @param string $path Full path to executable
     * @param array<string> $allowedArgs Optional list of allowed arguments
     * @return void
     */
    public static function register(string $name, string $path, array $allowedArgs = []): void
    {
        if (!file_exists($path) || !is_executable($path)) {
            throw new \RuntimeException("Command not found or not executable: $path");
        }

        self::$whitelist[$name] = [
            'path' => $path,
            'args' => $allowedArgs
        ];
    }

    /**
     * Execute a whitelisted command
     *
     * @param string $name Command alias
     * @param array<string> $args Command arguments
     * @param bool $captureOutput Whether to capture output (default: true)
     * @return array{output: string, exit_code: int}
     * @throws \RuntimeException If command not whitelisted or args invalid
     */
    public static function exec(string $name, array $args = [], bool $captureOutput = true): array
    {
        if (!isset(self::$whitelist[$name])) {
            throw new \RuntimeException("Command not whitelisted: $name");
        }

        $cmd = self::$whitelist[$name];

        // Validate arguments if whitelist is defined
        if (!empty($cmd['args'])) {
            foreach ($args as $arg) {
                if (!in_array($arg, $cmd['args'], true)) {
                    throw new \RuntimeException("Argument not allowed: $arg");
                }
            }
        }

        // Escape arguments
        $escapedArgs = array_map('escapeshellarg', $args);
        $fullCmd = $cmd['path'] . ' ' . implode(' ', $escapedArgs);

        // Execute
        $output = [];
        $exitCode = 0;

        if ($captureOutput) {
            exec($fullCmd, $output, $exitCode);
            return [
                'output' => implode("\n", $output),
                'exit_code' => $exitCode
            ];
        } else {
            passthru($fullCmd, $exitCode);
            return [
                'output' => '',
                'exit_code' => $exitCode
            ];
        }
    }

    /**
     * Check if a command is whitelisted
     *
     * @param string $name Command alias
     * @return bool
     */
    public static function isWhitelisted(string $name): bool
    {
        return isset(self::$whitelist[$name]);
    }

    /**
     * Get list of whitelisted commands
     *
     * @return array<string>
     */
    public static function getWhitelist(): array
    {
        return array_keys(self::$whitelist);
    }

    /**
     * Clear the whitelist
     *
     * @return void
     */
    public static function clearWhitelist(): void
    {
        self::$whitelist = [];
    }

    /**
     * Execute command and return only output
     *
     * @param string $name Command alias
     * @param array<string> $args Command arguments
     * @return string Command output
     */
    public static function run(string $name, array $args = []): string
    {
        $result = self::exec($name, $args);
        return $result['output'];
    }

    /**
     * Execute command and check if successful (exit code 0)
     *
     * @param string $name Command alias
     * @param array<string> $args Command arguments
     * @return bool True if exit code is 0
     */
    public static function test(string $name, array $args = []): bool
    {
        $result = self::exec($name, $args);
        return $result['exit_code'] === 0;
    }
}
