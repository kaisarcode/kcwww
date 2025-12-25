<?php
/**
 * HomeController - Default home page controller
 * Summary: Handles the main entry point page of the application.
 *
 * Author:  KaisarCode
 * Website: https://kaisarcode.com
 * License: GNU GPL v3.0
 * License URL: https://www.gnu.org/licenses/gpl-3.0.html
 */

/**
 * Home page controller.
 */
class HomeController extends Controller {
    /**
     * Render the home page.
     *
     * @return string Rendered HTML.
     */
    public static function index(): string {
        $home = DIR_APP . '/views/html/home.html';
        if (DIR_APP !== DIR_CORE && !is_file($home)) {
            $home = DIR_CORE . '/views/html/home.html';
        }
        return self::html($home);
    }
}
