<?php

/**
 * Core Routes - Route definitions for core controllers
 * Summary: Maps URL patterns to core controller methods
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

// Register global error handler
ErrorController::register();

// Home Route
Route::all('/', function () {
    return HomeController::index();
});

// Assets Routes
Route::all('/styles\.css', function () {
    return AssetsController::styles();
});

Route::all('/script\.js', function () {
    return AssetsController::script();
});

// PWA Routes
Route::all('/manifest\.json', function () {
    return PwaController::manifest();
});

Route::all('/worker\.js', function () {
    return PwaController::worker();
});

// Dynamic icon routes
$iconSizes = Conf::get('app.icon.sizes', [32, 192, 512]);
foreach ($iconSizes as $size) {
    Route::all("/appicon-$size\.png", function () use ($size) {
        return PwaController::icon($size);
    });
    Route::all("/favicon-$size\.png", function () use ($size) {
        return PwaController::favicon($size);
    });
}

// Apple Touch Icon specific
Route::all("/apple-touch-icon-180\.png", function () {
    return PwaController::icon(180);
});

// Dynamic image processing
// Pattern: /img/{path/to/name}-{dims}.{format}
Route::all('/img/([\w\d_/-]+)-(w\d+h\d+|w\d+|h\d+)\.(png|jpg|jpeg|webp)', function ($matches) {
    return ImagesController::process($matches);
});

// Generic asset server for files in views
Route::all('/(font|asset|file|img)/(.*)', function ($matches) {
    return AssetsController::file($matches[1] . '/' . $matches[2]);
});

// API Routes - Automatic Model Exposure
// Pattern: /api/{model}/{id}
Route::all('/api/([\w\d_-]+)/(\d+)', function ($matches) {
    return ApiController::handle($matches[1], $matches[2]);
});

// Pattern: /api/{model}
Route::all('/api/([\w\d_-]+)', function ($matches) {
    return ApiController::handle($matches[1]);
});
