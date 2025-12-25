<?php
/**
 * Model - Abstract base model class
 * Summary: Base class for database models using R.php ORM.
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
 * Abstract base model class.
 *
 * Extend this class to create database models.
 * Requires R.php RedBeanPHP to be loaded.
 */
abstract class Model implements JsonSerializable {
    /**
     * JSON serialization.
     *
     * @return mixed Array representation.
     */
    public function jsonSerialize(): mixed {
        return $this->toArray();
    }

    /**
     * Table name - override in child class.
     *
     * @var string
     */
    protected static string $table = '';

    /**
     * Database DSN - override in child class or set via init.
     *
     * @var string
     */
    protected static string $dsn = 'sqlite:/tmp/app.db';

    /**
     * Initialization flag.
     *
     * @var boolean
     */
    private static bool $initialized = false;

    /**
     * Bean instance.
     *
     * @var mixed
     */
    protected $bean;

    /**
     * Initialize R.php if not already loaded.
     *
     * @param string|null $dsn Database DSN optional.
     *
     * @return void
     */
    public static function init(?string $dsn = null): void {
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
     * Constructor.
     *
     * @param mixed $bean RedBean bean or null for new.
     */
    public function __construct(mixed $bean = null) {
        self::init();

        if ($bean) {
            $this->bean = $bean;
            $this->afterLoad();
        } else {
            $this->bean = R::dispense(static::$table);
        }
    }

    /**
     * Hook before saving.
     *
     * @return boolean True to continue, False to abort.
     */
    protected function beforeSave(): bool {
        return true;
    }

    /**
     * Hook after saving.
     *
     * @return void
     */
    protected function afterSave(): void {
    }

    /**
     * Hook before deleting.
     *
     * @return boolean True to continue, False to abort.
     */
    protected function beforeDelete(): bool {
        return true;
    }

    /**
     * Hook after deleting.
     *
     * @return void
     */
    protected function afterDelete(): void {
    }

    /**
     * Hook after loading.
     *
     * @return void
     */
    protected function afterLoad(): void {
    }

    /**
     * Get property from bean.
     *
     * @param string $name Property name.
     *
     * @return mixed Property value.
     */
    public function __get(string $name) {
        return $this->bean->$name ?? null;
    }

    /**
     * Set property on bean.
     *
     * @param string $name  Property name.
     * @param mixed  $value Property value.
     *
     * @return void
     */
    public function __set(string $name, mixed $value): void {
        $this->bean->$name = $value;
    }

    /**
     * Check if property is set.
     *
     * @param string $name Property name.
     *
     * @return boolean True if set.
     */
    public function __isset(string $name): bool {
        return isset($this->bean->$name);
    }

    /**
     * Get bean ID.
     *
     * @return integer Record ID.
     */
    public function getId(): int {
        return (int) $this->bean->id;
    }

    /**
     * Save model to database.
     *
     * @return integer ID of saved record.
     */
    public function save(): int {
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
     * Delete model from database.
     *
     * @return void
     */
    public function delete(): void {
        if (!$this->beforeDelete()) {
            return;
        }

        R::trash($this->bean);
        $this->afterDelete();
    }

    /**
     * Create new model instance.
     *
     * @param array $data Initial data.
     *
     * @return static New model instance.
     */
    public static function create(array $data = []): static {
        $model = new static();
        foreach ($data as $key => $value) {
            $model->$key = $value;
        }
        return $model;
    }

    /**
     * Find model by ID.
     *
     * @param integer $id Record ID.
     *
     * @return static|null Model or null if not found.
     */
    public static function find(int $id): ?static {
        static::init();
        $bean = R::load(static::$table, $id);
        if (!$bean->id) {
            return null;
        }
        return new static($bean);
    }

    /**
     * Find all models matching conditions.
     *
     * @param string $sql      SQL condition.
     * @param array  $bindings Parameter bindings.
     *
     * @return array Array of models.
     */
    public static function findAll(string $sql = '', array $bindings = []): array {
        static::init();
        $beans = R::find(static::$table, $sql, $bindings);
        $models = [];
        foreach ($beans as $bean) {
            $models[] = new static($bean);
        }
        return $models;
    }

    /**
     * Find first model matching conditions.
     *
     * @param string $sql      SQL condition.
     * @param array  $bindings Parameter bindings.
     *
     * @return static|null Model or null if not found.
     */
    public static function findFirst(string $sql = '', array $bindings = []): ?static {
        static::init();
        $bean = R::findOne(static::$table, $sql, $bindings);
        if (!$bean) {
            return null;
        }
        return new static($bean);
    }

    /**
     * Count models matching conditions.
     *
     * @param string $sql      SQL condition.
     * @param array  $bindings Parameter bindings.
     *
     * @return integer Count of matching records.
     */
    public static function count(string $sql = '', array $bindings = []): int {
        static::init();
        return R::count(static::$table, $sql, $bindings);
    }

    /**
     * Get paginated models.
     *
     * @param integer $page     Page number 1-indexed.
     * @param integer $limit    Items per page.
     * @param string  $sql      SQL condition.
     * @param array   $bindings Parameter bindings.
     * @param string  $order    ORDER BY clause.
     *
     * @return array Result and pagination info.
     */
    public static function paginate(
        int $page = 1,
        int $limit = 20,
        string $sql = '',
        array $bindings = [],
        string $order = ''
    ): array {
        static::init();

        $page = max(1, $page);
        $limit = max(1, min(100, $limit));
        $offset = ($page - 1) * $limit;

        // Get total count using only WHERE part
        $total = R::count(static::$table, $sql, $bindings);

        // Build query for find
        $orderSql = $sql ?: '1';
        if (!empty($order)) {
            $orderSql .= " ORDER BY $order";
        }
        $orderSql .= " LIMIT $limit OFFSET $offset";

        $beans = R::find(static::$table, $orderSql, $bindings);
        $models = [];
        foreach ($beans as $bean) {
            $models[] = new static($bean);
        }

        $totalPages = (int) ceil($total / $limit);

        return [
            'result' => $models,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'total_pages' => $totalPages,
                'has_next' => $page < $totalPages,
                'has_prev' => $page > 1
            ]
        ];
    }

    /**
     * Get all models.
     *
     * @return array Array of all models.
     */
    public static function all(): array {
        return static::findAll();
    }

    /**
     * Export model to array.
     *
     * @return array Model data.
     */
    public function toArray(): array {
        return $this->bean->export();
    }
}
