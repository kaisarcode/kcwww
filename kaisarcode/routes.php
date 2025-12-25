<?php
/**
 * KaisarCode Routes
 * Summary: Site-specific route definitions.
 *
 * Author:  KaisarCode
 * Website: https://kaisarcode.com
 * License: GNU GPL v3.0
 * License URL: https://www.gnu.org/licenses/gpl-3.0.html
 */

// RSS Route
Route::all('/rss\.xml', function () {
    return RssController::handle();
});

// Dynamic Page Loader
Route::all('/(.*)', function ($matches) {
    return PageController::handle($matches);
});
