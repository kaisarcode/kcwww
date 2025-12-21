<?php
/**
 * RssController - Dynamic RSS feed generator
 * Summary: Generates paginated RSS feeds for documents with caching support.
 *
 * Author:  KaisarCode
 * Website: https://kaisarcode.com
 * License: GNU GPL v3.0
 */

class RssController extends Controller
{
    /**
     * Handle RSS request
     */
    public static function handle(): void
    {
        $path = Http::getHttpVar('path', '/');
        // Normalize path
        if ($path !== '/' && str_ends_with($path, '/')) {
            $path = rtrim($path, '/');
        }
        if ($path !== '/' && !str_starts_with($path, '/')) {
            $path = '/' . $path;
        }

        // Use p and l but map to page and limit for Model::paginate
        $page = (int) Http::getHttpVar('p', 1);
        $limit = (int) Http::getHttpVar('l', 20);

        // Load base document if exists for better title/desc
        $baseDoc = DocModel::findFirst('path = ? AND active = ?', [$path, 1]);

        // Build query logic
        $prefix = $path === '/' ? '/' : $path . '/';
        $like = $prefix === '/' ? '/%' : $prefix . '%';
        $notLast = $prefix . '%/%';

        $sql = "path LIKE ? AND active = ? AND path NOT LIKE ? AND path != ?";
        $bindings = [$like, 1, $notLast, $path];

        // Fetch data using DocModel
        $res = DocModel::paginate($page, $limit, $sql, $bindings, 'date_add DESC');
        
        $siteUrl = Http::getBaseUri();
        $items = [];
        $lastBuildTimestamp = 0;

        foreach ($res['result'] as $model) {
            $item = $model->toArray();
            $ts = strtotime($item['date_add']);
            if ($ts > $lastBuildTimestamp) {
                $lastBuildTimestamp = $ts;
            }
            $item['pub_date'] = $ts ? date(DATE_RSS, $ts) : '';
            $items[] = $item;
        }

        $lastBuildDate = $lastBuildTimestamp ? date(DATE_RSS, $lastBuildTimestamp) : date(DATE_RSS);
        
        // Site metadata defaults
        $rssTitle = Conf::get('app.name');
        $rssDesc = Conf::get('app.desc');

        if ($baseDoc) {
            if ($path !== '/') {
                $rssTitle = $baseDoc->title;
            }
            $rssDesc = $baseDoc->desc;
        }

        // Handle pagination for legacy XML format
        $pagination = $res['pagination'];
        $data = [
            'items' => $items,
            'pagination' => [
                'page' => $pagination['page'],
                'total_pages' => $pagination['total_pages'],
                'limit' => $pagination['limit'],
                'total' => $pagination['total'],
                'nextPage' => $pagination['has_next'] ? $pagination['page'] + 1 : '',
                'prevPage' => $pagination['has_prev'] ? $pagination['page'] - 1 : '',
            ],
            'path' => $path,
            'siteUrl' => $siteUrl,
            'rssTitle' => $rssTitle,
            'rssDesc' => $rssDesc,
            'lastBuildDate' => $lastBuildDate,
        ];

        // Render using template
        $tplFile = DIR_APP . '/views/html/rss.xml';
        
        Http::setHeaderXml();
        echo self::html($tplFile, $data);
    }

    /**
     * Internal HTML renderer (XML in this case)
     */
    protected static function html(string $template, array $data = []): string
    {
        $cfg = Conf::get('tpl.conf', []);
        $tpl = new Template($cfg);
        $merged = array_merge(Conf::all(), $data);
        return $tpl->parse($template, $merged);
    }
}
