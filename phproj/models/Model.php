<?php
/**
 * Model - Abstract base model class
 * Summary: Base class for database models using R.php ORM
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
 * Abstract base model class
 *
 * Extend this class to create database models.
 * Requires R.php (RedBeanPHP) to be loaded.
 *
 * Example:
 * ```php
 * class User extends Model
 * {
 *     protected static string $table = 'users';
 * }
 *
 * $user = User::create(['name' => 'John']);
 * $user->email = 'john@example.com';
 * $user->save();
 * ```
 */
abstract class Model
{
    /**
     * Table name - override in child class
     */
    protected static string $table = '';

    /**
     * Database DSN - override in child class or set via init()
     */
    protected static string $dsn = 'sqlite:/tmp/app.db';

    /**
     * Initialization flag
     */
    private static bool $initialized = false;

    /**
     * Bean instance
     */
    protected $bean;

    /**
     * Initialize R.php if not already loaded
     *
     * @param string|null $dsn Database DSN (optional)
     */
    public static function init(?string $dsn = null): void
    {
        if (self::$initialized) {
            return;
        }

        // Load R.php if not loaded
        if (!class_exists('R')) {
            require_once __DIR__ . '/../classes/vnd/R.php';
        }

        // Setup database
        $connectionDsn = $dsn ?? static::$dsn;
        R::setup($connectionDsn);
        R::freeze(false);

        self::$initialized = true;
    }

    /**
     * Constructor
     *
     * @param mixed $bean RedBean bean or null for new
     */
    public function __construct($bean = null)
    {
        self::init();

        if ($bean) {
            $this->bean = $bean;
            $this->afterLoad();
        } else {
            $this->bean = R::dispense(static::$table);
        }
    }

    /**
     * Hook before saving
     *
     * @return bool True to continue, False to abort
     */
    protected function beforeSave(): bool
    {
        return true;
    }

    /**
     * Hook after saving
     */
    protected function afterSave(): void
    {
    }

    /**
     * Hook before deleting
     *
     * @return bool True to continue, False to abort
     */
    protected function beforeDelete(): bool
    {
        return true;
    }

    /**
     * Hook after deleting
     */
    protected function afterDelete(): void
    {
    }

    /**
     * Hook after loading
     */
    protected function afterLoad(): void
    {
    }

    /**
     * Get property from bean
     *
     * @param string $name
     * @return mixed
     */
    public function __get(string $name)
    {
        return $this->bean->$name ?? null;
    }

    /**
     * Set property on bean
     *
     * @param string $name
     * @param mixed $value
     */
    public function __set(string $name, $value): void
    {
        $this->bean->$name = $value;
    }

    /**
     * Check if property is set
     *
     * @param string $name
     * @return bool
     */
    public function __isset(string $name): bool
    {
        return isset($this->bean->$name);
    }

    /**
     * Get bean ID
     *
     * @return int
     */
    public function getId(): int
    {
        return (int) $this->bean->id;
    }

    /**
     * Save model to database
     *
     * @return int ID of saved record
     */
    public function save(): int
    {
        if (!$this->beforeSave()) {
            return 0;
        }

        // Auto-fields
        if (!$this->bean->id && !isset($this->bean->active)) {
            $this->bean->active = 1;
        }
        if (!$this->bean->id && !isset($this->bean->protected)) {
            $this->bean->protected = 0;
        }
        if (!$this->bean->id && !isset($this->bean->date_add)) {
            $this->bean->date_add = date('Y-m-d H:i:s');
        }
        $this->bean->date_upd = date('Y-m-d H:i:s');

        $id = R::store($this->bean);
        $this->afterSave();

        return $id;
    }

    /**
     * Delete model from database
     */
    public function delete(): void
    {
        if (!$this->beforeDelete()) {
            return;
        }

        R::trash($this->bean);
        $this->afterDelete();
    }

    /**
     * Create new model instance
     *
     * @param array $data Initial data
     * @return static
     */
    public static function create(array $data = []): static
    {
        $model = new static();
        foreach ($data as $key => $value) {
            $model->$key = $value;
        }
        return $model;
    }

    /**
     * Find model by ID
     *
     * @param int $id
     * @return static|null
     */
    public static function find(int $id): ?static
    {
        $bean = R::load(static::$table, $id);
        if (!$bean->id) {
            return null;
        }
        return new static($bean);
    }

    /**
     * Find all models matching conditions
     *
     * @param string $sql SQL condition
     * @param array $bindings
     * @return array
     */
    public static function findAll(string $sql = '', array $bindings = []): array
    {
        $beans = R::find(static::$table, $sql, $bindings);
        $models = [];
        foreach ($beans as $bean) {
            $models[] = new static($bean);
        }
        return $models;
    }

    /**
     * Find first model matching conditions
     *
     * @param string $sql SQL condition
     * @param array $bindings
     * @return static|null
     */
    public static function findFirst(string $sql = '', array $bindings = []): ?static
    {
        $bean = R::findOne(static::$table, $sql, $bindings);
        if (!$bean) {
            return null;
        }
        return new static($bean);
    }

    /**
     * Count models matching conditions
     *
     * @param string $sql SQL condition
     * @param array $bindings
     * @return int
     */
    public static function count(string $sql = '', array $bindings = []): int
    {
        return R::count(static::$table, $sql, $bindings);
    }

    /**
     * Get all models
     *
     * @return array
     */
    public static function all(): array
    {
        return static::findAll();
    }

    /**
     * Export model to array
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->bean->export();
    }
}
