<?php
/**
 * Cookie - Cookie handler
 * Summary: Provides secure cookie management with encryption support
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
 * Cookie handler
 */
class Cookie
{

    /**
     * Sets a cookie.
     *
     * @param string $name Cookie name
     * @param string $value Cookie value
     * @param int|null $expires Duration in seconds from now. Null = session cookie.
     * @param string|null $key Encryption key. If provided, value will be encrypted.
     * @return void
     */
    public static function set(string $name, string $value, ?int $expires = null, ?string $key = null): void
    {
        $expireTime = ($expires > 0) ? time() + $expires : 0;

        // Encrypt value if key is provided
        $finalValue = ($key !== null && $value !== '') ? self::encrypt($value, $key) : $value;

        setcookie(
            $name,
            $finalValue,
            [
                'expires' => $expireTime,
                'path' => '/',
                'secure' => self::isHttps(),
                'httponly' => true,
                'samesite' => 'Lax'
            ]
        );
        $_COOKIE[$name] = $finalValue;
    }

    /**
     * Retrieves a cookie value.
     *
     * @param string $name Cookie name
     * @param string|null $key Decryption key. If provided, value will be decrypted.
     * @return string|false Cookie value or false if not found
     */
    public static function get(string $name, ?string $key = null): string|false
    {
        $value = $_COOKIE[$name] ?? false;

        if ($value === false || $value === '') {
            return $value;
        }

        // Decrypt value if key is provided
        if ($key !== null) {
            $decrypted = self::decrypt($value, $key);
            return $decrypted !== '' ? $decrypted : false;
        }

        return $value;
    }

    /**
     * Checks if a cookie exists.
     *
     * @param string $name Cookie name
     * @return bool
     */
    public static function has(string $name): bool
    {
        return isset($_COOKIE[$name]);
    }

    /**
     * Deletes a cookie.
     *
     * @param string $name Cookie name
     * @return void
     */
    public static function delete(string $name): void
    {
        setcookie($name, '', [
            'expires' => time() - 3600,
            'path' => '/',
            'secure' => self::isHttps(),
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
        unset($_COOKIE[$name]);
    }

    /**
     * Checks if request is using HTTPS.
     *
     * @return bool
     */
    private static function isHttps(): bool
    {
        return isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    }

    /**
     * Encrypts a value using AES-256-CBC.
     *
     * @param string $value Value to encrypt
     * @param string $key Encryption key
     * @return string Base64-encoded encrypted value with IV
     */
    private static function encrypt(string $value, string $key): string
    {
        if ($value === '')
            return '';
        $iv = random_bytes(16);
        $encrypted = openssl_encrypt($value, 'AES-256-CBC', hash('sha256', $key, true), 0, $iv);
        return base64_encode($iv . $encrypted);
    }

    /**
     * Decrypts a value using AES-256-CBC.
     *
     * @param string $encrypted Base64-encoded encrypted value with IV
     * @param string $key Encryption key
     * @return string Decrypted value or empty string on failure
     */
    private static function decrypt(string $encrypted, string $key): string
    {
        if ($encrypted === '')
            return '';
        $data = base64_decode($encrypted);
        if ($data === false || strlen($data) < 16)
            return '';
        $iv = substr($data, 0, 16);
        $ciphertext = substr($data, 16);
        $decrypted = openssl_decrypt($ciphertext, 'AES-256-CBC', hash('sha256', $key, true), 0, $iv);
        return $decrypted !== false ? $decrypted : '';
    }
}
