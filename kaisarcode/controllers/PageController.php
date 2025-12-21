<?php
/**
 * PageController - Dynamic page loader
 * Summary: Loads documents from the database based on the request path.
 */

class PageController extends Controller
{
    /**
     * Handle dynamic page request
     *
     * @param array $matches Regex matches from route
     * @return string Rendered HTML
     */
    public static function handle(array $matches = [])
    {
        $path = $matches[0] ?? '/';
        
        // Normalize path
        if ($path !== '/' && str_ends_with($path, '/')) {
            $path = rtrim($path, '/');
        }

        // Search for document by path
        DocModel::init();
        $doc = R::findOne('kcdoc', 'path = ? AND active = ?', [$path, 1]);

        if (!$doc) {
            // Return false to continue to next route (core routes)
            return false;
        }

        // Prepare data for view
        $data = [
            'doc' => new DocModel($doc),
            'title' => $doc->title,
            'content' => $doc->cont
        ];

        // Reuse home.html as requested
        return self::html(DIR_APP . '/views/html/home.html', $data);
    }
}
