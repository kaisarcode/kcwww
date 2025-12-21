<?php
/**
 * KaisarCode Routes - Site-specific route definitions
 */

// RSS Route (Dynamic)
Route::all('/rss\.xml', function () {
    return RssController::handle();
});
