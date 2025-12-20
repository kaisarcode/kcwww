<?php
/**
 * Dummy Site - Configuration Overrides
 * Summary: Site-specific configuration that overrides core defaults.
 *
 * Author:  KaisarCode
 * Website: https://kaisarcode.com
 * License: GNU GPL v3.0
 * License URL: https://www.gnu.org/licenses/gpl-3.0.html
 */

// Override only what differs from core
Conf::set([
    'app.name' => 'Dummy Site',
    'app.desc' => 'A demonstration child site extending phproj core',
    'app.color' => '#2a4a6a',
    'assets.css.entry' => VIEWS_OVERRIDE . '/css/styles.css',
]);
