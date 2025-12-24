<?php
/**
 * ApiController - Generic API handler for models
 * Summary: Automatically exposes models via REST-like API
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
 * Generic API Controller
 *
 * Exposes all models to the API automatically.
 * Routes:
 * - GET /api/{model}          -> List all
 * - GET /api/{model}/{id}     -> Get one
 * - POST /api/{model}         -> Create
 * - POST /api/{model}/{id}    -> Update
 * - DELETE /api/{model}/{id}  -> Delete
 */
class ApiController extends Controller
{
    /**
     * Handle API request
     *
     * @param string $modelName Name of the model (from URL)
     * @param string|null $id Optional ID
     */
    public static function handle(string $modelName, ?string $id = null): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        if (empty($modelName) || $modelName === 'status') {
             self::render('ok', null);
            return;
        }

        $className = self::getModelClass($modelName);

        if (!$className || !class_exists($className)) {
            self::status(404);
            self::render('error', null, [['message' => 'Model not found: ' . $modelName]]);
            return;
        }

        try {
            switch ($method) {
                case 'GET':
                    if ($id) {
                        self::getOne($className, (int) $id);
                    } else {
                        self::listAll($className);
                    }
                    break;

                case 'POST':
                    if ($id) {
                        self::update($className, (int) $id);
                    } else {
                        self::create($className);
                    }
                    break;

                case 'DELETE':
                    if ($id) {
                        self::remove($className, (int) $id);
                    } else {
                        self::status(400);
                        self::render('error', null, [['message' => 'ID required for deletion']]);
                    }
                    break;

                default:
                    self::status(405);
                    self::render('error', null, [['message' => 'Method not allowed']]);
                    break;
            }
        } catch (Exception $e) {
            self::status(500);
            self::render('error', null, [[
                'message' => 'Internal server error',
                'detail' => self::isDev() ? $e->getMessage() : null
            ]]);
        }
    }

    /**
     * List records with pagination
     *
     * @param string $className
     */
    private static function listAll(string $className): void
    {
        $page = (int) ($_GET['page'] ?? 1);
        $limit = (int) ($_GET['limit'] ?? 20);

        $res = $className::paginate($page, $limit);
        $data = array_map(fn($m) => $m->toArray(), $res['result']);

        self::render('ok', $data, [], $res['pagination']);
    }

    /**
     * Get one record by ID
     *
     * @param string $className
     * @param int $id
     */
    private static function getOne(string $className, int $id): void
    {
        $model = $className::find($id);
        if (!$model) {
            self::status(404);
            self::render('error', null, [['message' => 'Record not found']]);
            return;
        }
        self::render('ok', $model->toArray());
    }

    /**
     * Create new record
     *
     * @param string $className
     */
    private static function create(string $className): void
    {
        $data = self::params();
        $model = $className::create($data);
        $id = $model->save();

        if ($id > 0) {
            self::status(201);
            self::render('ok', $model->toArray());
        } else {
            self::status(400);
            self::render('error', null, [['message' => 'Failed to create record']]);
        }
    }

    /**
     * Update existing record
     *
     * @param string $className
     * @param int $id
     */
    private static function update(string $className, int $id): void
    {
        $model = $className::find($id);
        if (!$model) {
            self::status(404);
            self::render('error', null, [['message' => 'Record not found']]);
            return;
        }

        $data = self::params();
        foreach ($data as $key => $value) {
            $model->$key = $value;
        }

        if ($model->save()) {
            self::render('ok', $model->toArray());
        } else {
            self::status(400);
            self::render('error', null, [['message' => 'Failed to update record']]);
        }
    }

    /**
     * Remove record
     *
     * @param string $className
     * @param int $id
     */
    private static function remove(string $className, int $id): void
    {
        $model = $className::find($id);
        if (!$model) {
            self::status(404);
            self::render('error', null, [['message' => 'Record not found']]);
            return;
        }

        $model->delete();
        self::render('ok', ['success' => true]);
    }

    /**
     * Map model name from URL to class name
     * Example: "dummy" -> "DummyModel"
     *
     * @param string $name
     * @return string|null
     */
    private static function getModelClass(string $name): ?string
    {
        // Simple mapping: capitalize first letter + "Model"
        // If "DummyModel" exists, use it.
        $class = ucfirst(strtolower($name)) . 'Model';

        if (class_exists($class)) {
            return $class;
        }

        // Fallback: try just capitalized (e.g. "User")
        $class = ucfirst(strtolower($name));
        if (class_exists($class)) {
            return $class;
        }

        return null;
    }
    /**
     * Standardized API response
     *
     * @param string $status 'ok' or 'error'
     * @param mixed $result Data to return
     * @param array $errors List of errors
     * @param array|null $pagination Pagination info
     */
    protected static function render(string $status, $result = null, array $errors = [], ?array $pagination = null): void
    {
        echo self::json([
            'status' => $status,
            'result' => $result,
            'pagination' => $pagination,
            'errors' => $errors
        ]);
    }
}
