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
        $doc = DocModel::findFirst('path = ? AND active = ?', [$path, 1]);

        if (!$doc && $path !== '/') {
            return false;
        }

        // Params for Explore with cookie persistence
        $page = (int) self::param('p', 1);
        $limit = (int) self::param('l', 10);
        
        $mode = (string) self::param('mode');
        if ($mode === '') {
            $mode = Http::getHttpVar('explore_mode', 'docs');
        } else {
            setcookie('explore_mode', $mode, time() + 86400 * 30, '/');
        }

        $order = (string) self::param('order');
        if ($order === '') {
            $order = Http::getHttpVar('explore_order', 'asc');
        } else {
            setcookie('explore_order', $order, time() + 86400 * 30, '/');
        }

        // Explore items
        $explore = DocModel::explore($path, $page, $limit, $mode, $order);
        
        // Content rendering - fallback to desc for tweet-style docs
        $parsedown = new ParsedownExtra();
        $content = $doc && $doc->cont ? $parsedown->text($doc->cont) : ($doc ? '<p>' . htmlspecialchars($doc->desc) . '</p>' : '');

        // Helper for building explore URLs
        $buildUrl = function(array $overrides) {
            return Http::getPathUri() . '?' . Http::getQueryString($overrides);
        };

        // Format explore items
        $items = [];
        foreach ($explore['result'] as $item) {
            $parsedownItem = new ParsedownExtra();
            $preview = $item->desc ?: Str::truncate(strip_tags($parsedownItem->text($item->cont)), 200);
            
            $items[] = [
                'title' => $item->title ?: $item->path,
                'desc' => $preview,
                'url' => $item->path,
                'image' => DocModel::convertImageUrl($item->image),
                'date' => $item->date_add,
                'tags' => explode(',', $item->tags ?: ''),
                'icon' => (DocModel::count('path LIKE ? AND path != ?', [$item->path . '/%', $item->path]) > 0) ? 'folder' : 'file'
            ];
        }

        // View data
        $data = [
            'doc' => $doc,
            'title' => $doc ? ($doc->title ?: $doc->path) : Conf::get('app.name'),
            'content' => $content,
            'explore' => [
                'items' => $items,
                'pagination' => array_merge($explore['pagination'], [
                    'first_url' => $buildUrl(['p' => 1]),
                    'prev_url'  => $explore['pagination']['has_prev'] ? $buildUrl(['p' => $page - 1]) : '',
                    'next_url'  => $explore['pagination']['has_next'] ? $buildUrl(['p' => $page + 1]) : '',
                    'last_url'  => $buildUrl(['p' => $explore['pagination']['total_pages']])
                ]),
                'mode' => [
                    'current' => $mode,
                    'url' => $buildUrl(['mode' => ($mode === 'docs' ? 'blog' : 'docs'), 'p' => 1])
                ],
                'order' => [
                    'current' => $order,
                    'url' => $buildUrl(['order' => ($order === 'asc' ? 'desc' : 'asc'), 'p' => 1])
                ]
            ]
        ];

        return self::html(DIR_APP . '/views/html/page.html', $data);
    }
}
