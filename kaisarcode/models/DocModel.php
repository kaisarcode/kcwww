<?php
/**
 * DocModel
 * Summary: Model for interacting with the doc.sqlite database records.
 *
 * Author:  KaisarCode
 * Website: https://kaisarcode.com
 * License: GNU GPL v3.0
 * License URL: https://www.gnu.org/licenses/gpl-3.0.html
 */

/**
 * Doc Model mapped to table docs.
 */
class DocModel extends Model {
    /**
     * Table name.
     *
     * @var string
     */
    protected static string $table = 'kcdoc';

    /**
     * Init with site-specific DSN.
     *
     * @param string|null $dsn Database connection string.
     *
     * @return void
     */
    public static function init(?string $dsn = null): void {
        parent::init($dsn ?? DSN_DOC);
    }

    /**
     * Get children documents for Explore.
     *
     * @param string  $parentPath Parent path to search from.
     * @param integer $page       Page number.
     * @param integer $limit      Items per page.
     * @param string  $mode       Mode: docs or blog.
     * @param string  $order      Order: asc or desc.
     *
     * @return array Paginated results.
     */
    public static function explore(
        string $parentPath = '/',
        int $page = 1,
        int $limit = 10,
        string $mode = 'docs',
        string $order = 'asc'
    ): array {
        static::init();

        $prefix = $parentPath === '/' ? '/' : $parentPath . '/';
        $like = $prefix . '%';

        $bindings = [':parent' => $parentPath, ':like' => $like];
        $where = "path LIKE :like AND path != :parent AND active = 1";

        if ($mode === 'docs') {
            $where .= " AND path NOT LIKE :notlike";
            $bindings[':notlike'] = $prefix . '%/%';
        }

        // Folder first logic
        $isFolderSql = "(EXISTS (SELECT 1 FROM kcdoc AS c ";
        $isFolderSql .= "WHERE c.path LIKE kcdoc.path || '/%' ";
        $isFolderSql .= "AND c.path != kcdoc.path AND c.active = 1))";

        $folderSort = $order === 'asc' ? 'DESC' : 'ASC';

        $orderSql = "$isFolderSql $folderSort, path " . strtoupper($order);
        return static::paginate($page, $limit, $where, $bindings, $orderSql);
    }

    /**
     * Convert document image URL to preview version.
     *
     * @param string|null $url Original image URL.
     *
     * @return string Converted preview URL.
     */
    public static function convertImageUrl(?string $url): string {
        $url = trim((string)$url);
        if ($url === '') {
            return '';
        }

        $lower = strtolower($url);
        if (!str_starts_with($lower, '/img/kcdoc/')) {
            return $url;
        }

        $parts = parse_url($url);
        if ($parts === false) {
            return $url;
        }

        $path = $parts['path'] ?? $url;

        // Remove size suffix and convert to webp
        $pattern = '/-(w\\d+h\\d+|w\\d+|h\\d+)(?=\\.[^.]+$)/i';
        $path = preg_replace($pattern, '', $path);
        $path = preg_replace('/\\.(png|jpe?g|webp|svg)$/i', '.webp', $path);

        $previewSize = Conf::get('app.image.preview.size', 500);
        $path = preg_replace('/\\.webp$/i', '-h' . $previewSize . '.webp', $path);

        $query = isset($parts['query']) ? ('?' . $parts['query']) : '';
        $fragment = isset($parts['fragment']) ? ('#' . $parts['fragment']) : '';

        return $path . $query . $fragment;
    }
}
