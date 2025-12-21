<?php
/**
 * HomeController - Default home page controller
 * Summary: Handles the main entry point page of the application.
 *
 * Author:  KaisarCode
 * Website: https://kaisarcode.com
 * License: GNU GPL v3.0
 */

/**
 * Home page controller
 */
class HomeController extends Controller
{
    /**
     * Render the home page
     *
     * @return string
     */
    public static function index(): string
    {
        $home = (DIR_APP !== DIR_CORE && is_file(DIR_APP . '/views/html/home.html'))
            ? DIR_APP . '/views/html/home.html'
            : DIR_CORE . '/views/html/home.html';
        return self::html($home);
    }
}
