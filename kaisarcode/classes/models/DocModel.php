<?php
/**
 * DocModel - Document Model
 * Summary: Model for interacting with the doc.sqlite database records.
 *
 * Author:  KaisarCode
 * Website: https://kaisarcode.com
 * License: GNU GPL v3.0
 */

/**
 * Doc Model
 * Mapped to table: docs
 */
class DocModel extends Model
{
    /**
     * Table name
     */
    protected static string $table = 'docs';

    /**
     * Override init to set specific DSN for this model
     *
     * @param string|null $dsn
     */
    public static function init(?string $dsn = null): void
    {
        // Use site-specific DSN defined in conf.php
        parent::init($dsn ?? DSN_DOC);
    }
}
