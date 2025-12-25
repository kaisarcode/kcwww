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

define('DSN_DOC', 'sqlite:' . DIR_VAR . '/data/db/doc.sqlite');

Conf::set([
    'app.id' => 'kaisarcode',
    'app.name' => 'KaisarCode',
    'page.name' => 'KaisarCode',
    'app.desc' => 'KaisarCode es un repositorio de documentos dinámicos, volátiles, y ligeramente categorizados.',
    'app.keywords' => 'kaisarcode, software, opinion, analitica, articulos',
    'app.lang' => 'es',
    'app.color' => '#222222',
    'VIEWS' => DIR_APP . '/views',
    'assets.css.entry' => DIR_APP . '/views/css/styles.css',
    'app.image.src_dir' => DIR_APP . '/views/img',
    'app.image.preview.size' => 200,
]);
