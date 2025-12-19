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
        return self::html(VIEWS . '/html/home.html');
    }
}
