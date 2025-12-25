<?php
/**
 * ErrorController - Global error handler
 * Summary: Handles HTTP errors with JSON for API routes and HTML otherwise.
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
 * Global error handler controller.
 */
class ErrorController extends Controller {
    /**
     * HTTP status descriptions.
     *
     * @var array
     */
    private static array $descriptions = [
        400 => 'Bad Request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        408 => 'Request Timeout',
        429 => 'Too Many Requests',
        500 => 'Internal Server Error',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
    ];

    /**
     * Handle error response.
     *
     * @param integer $code HTTP status code.
     *
     * @return string Rendered error response.
     */
    public static function handle(int $code): string {
        $desc = self::$descriptions[$code] ?? 'Error';
        $path = Http::getPathUri();

        self::status($code);

        // API routes get JSON response
        if (str_starts_with($path, '/api')) {
            return self::json([
                'status' => 'error',
                'result' => null,
                'errors' => [
                    [
                        'code' => $code,
                        'message' => $desc,
                    ]
                ],
            ]);
        }

        // Web routes get HTML response
        Conf::set('page.name', (string) $code);
        Conf::set('page.desc', $desc);
        Conf::set('page.cont', $desc);

        // Explicit error variables for templates
        Conf::set('error.code', $code);
        Conf::set('error.message', $desc);

        // Resolve template strictly within html directory
        $template = DIR_APP . '/views/html/error.html';
        if (DIR_APP !== DIR_CORE && !is_file($template)) {
            $template = DIR_CORE . '/views/html/error.html';
        }

        if (is_file($template)) {
            return self::html($template);
        }

        // Fallback plain response
        return "<h1>$code</h1><p>$desc</p>";
    }

    /**
     * Register as Route error handler.
     *
     * @return void
     */
    public static function register(): void {
        Route::error(function (int $code) {
            echo ErrorController::handle($code);
        });
    }
}
