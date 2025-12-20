<?php
/**
 * KaisarCode Site - Configuration Overrides
 * Summary: Site-specific configuration that overrides core defaults.
 *
 * Author:  KaisarCode
 * Website: https://kaisarcode.com
 * License: GNU GPL v3.0
 * License URL: https://www.gnu.org/licenses/gpl-3.0.html
 */

Conf::set([
    'app.name' => 'KaisarCode',
    'app.desc' => 'KaisarCode es un repositorio de documentos dinámicos, volátiles, y ligeramente categorizados.',
    'app.color' => '#222222',
    'assets.css.entry' => VIEWS_OVERRIDE . '/css/styles.css',
    'app.image.src_dir' => VIEWS_OVERRIDE . '/img',
]);
