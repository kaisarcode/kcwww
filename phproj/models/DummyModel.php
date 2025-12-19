<?php
/**
 * DummyModel - Example database model
 * Summary: A reference implementation of the Model class for demonstration
 *
 * Author:  KaisarCode
 * Website: https://kaisarcode.com
 * License: GNU GPL v3.0
 * License URL: https://www.gnu.org/licenses/gpl-3.0.html
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License.
 */

/**
 * Example model implementation
 *
 * Demonstrates:
 * - Table name definition
 * - Custom DSN (optional)
 * - Using hooks (beforeSave, afterLoad)
 */
class DummyModel extends Model
{
    /**
     * Table name for this model
     */
    protected static string $table = 'dummy';

    /**
     * Custom DSN for this specific model (optional)
     * If not set, it defaults to sqlite:/tmp/app.db
     */
    protected static string $dsn = 'sqlite:/tmp/phproj-dummy.db';

    /**
     * Hook before saving
     *
     * In this example, we automatically set a timestamp
     * and ensure the 'name' field is not empty.
     */
    protected function beforeSave(): bool
    {
        if (empty($this->name)) {
            return false;
        }

        if (empty($this->created_at)) {
            $this->created_at = time();
        }

        $this->updated_at = time();
        return true;
    }

    /**
     * Hook after loading
     *
     * For example, formatting a date for easy use
     */
    protected function afterLoad(): void
    {
        $this->formatted_date = date('Y-m-d H:i:s', $this->created_at);
    }
}
