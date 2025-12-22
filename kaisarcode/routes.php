<?php
/**
 * KaisarCode Routes - Site-specific route definitions
 */

// RSS Route (Dynamic)
Route::all('/rss\.xml', function () {
    return RssController::handle();
});

// Dynamic Page Loader
// Match everything and check database. If not found, return false to let core routes handle it.
Route::all('/(.*)', function ($matches) {
    return PageController::handle($matches);
});
