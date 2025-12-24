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
    protected static string $table = 'kcdoc';

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

    /**
     * Get children documents (Explore)
     *
     * @param string $parentPath
     * @param int $page
     * @param int $limit
     * @param string $mode 'docs' (immediate) or 'blog' (recursive)
     * @param string $order 'asc' or 'desc'
     * @return array
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
            // Immediate children only (no more slashes after prefix)
            $where .= " AND path NOT LIKE :notlike";
            $bindings[':notlike'] = $prefix . '%/%';
        }

        // Folder first logic
        $isFolderSql = "(EXISTS (SELECT 1 FROM kcdoc AS c WHERE c.path LIKE kcdoc.path || '/%' AND c.path != kcdoc.path AND c.active = 1))";
        $folderSort = $order === 'asc' ? 'DESC' : 'ASC'; // Folders (1) > Files (0)
        
        $orderSql = "$isFolderSql $folderSort, path " . strtoupper($order);
        return static::paginate($page, $limit, $where, $bindings, $orderSql);
    }

    /**
     * Convert document image URL to preview version
     *
     * @param string|null $url
     * @return string
     */
    public static function convertImageUrl(?string $url): string
    {
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
        // Remove existing size suffix and convert to webp preview
        $path = preg_replace('/-(w\\d+h\\d+|w\\d+|h\\d+)(?=\\.[^.]+$)/i', '', $path);
        $path = preg_replace('/\\.(png|jpe?g|webp|svg)$/i', '.webp', $path);
        
        $previewSize = Conf::get('app.image.preview.size', 500);
        $path = preg_replace('/\\.webp$/i', '-h' . $previewSize . '.webp', $path);
        
        $query = isset($parts['query']) ? ('?' . $parts['query']) : '';
        $fragment = isset($parts['fragment']) ? ('#' . $parts['fragment']) : '';
        
        return $path . $query . $fragment;
    }
}
