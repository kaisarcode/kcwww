<?php
/**
 * Template - Custom template engine
 * Summary: Parses and compiles custom template clauses into executable PHP with caching and inheritance support
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
 * Template engine
 *
 * Parses and compiles custom template clauses into executable PHP.
 * Supports caching, includes, inheritance, scoped blocks, and variable injection.
 * Designed for minimalism and full control without external dependencies.
 */
class Template {
    public string $cache_dir;
    public bool $cache_enabled;
    public bool $trace_files;
    private array $clauses = [];
    private string $id_context;

    /**
     * Constructor.
     *
     * @param array|object $conf Optional configuration: cache_dir, cache_enabled, trace_files
     */
    public function __construct(array|object $conf = []) {
        $conf = (array) $conf;
        $this->cache_dir = $conf['cache_dir'] ?? sys_get_temp_dir();
        $this->cache_enabled = $conf['cache_enabled'] ?? true;
        $this->trace_files = $conf['trace_files'] ?? false;
        $this->setClauses();
    }

    /**
     * @return array List of registered clause types
     */
    private function getClauseNames(): array {
        $names = [];
        foreach ($this->clauses as $k => $v) {
            $names[] = $k;
        }
        return $names;
    }

    /**
     * Registers template clause patterns.
     *
     * @param array $base_clauses List of clause types to register
     */
    private function setClauses(): void {
        $this->clauses['include'] = "/{{@\s*(include)\s+([^\s\}]+)(?:\s+(.*?))?\s*}}/is";
        $this->clauses['var'] = "/{{@\s*(var)\s+([^\s\}]+)(?:\s+(.*?))?\s*}}/is";
        $this->clauses['setblock'] = "/{{@\s*(setblock)\b(.*?)}}(.*?){{@\s*endsetblock\b}}/is";
        $this->clauses['block'] = "/{{@\s*(block)\s+([^\s\}]+)(?:\s+(.*?))?\s*}}/is";
    }

    /**
     * Finds all matches of a clause type in the code.
     *
     * @param string $type Clause type
     * @param string $code Raw template code
     * @return array Matched clause definitions
     */
    private function matchClause(string $type, string $code): array {
        if (str_starts_with($type, 'set')) {
            $core = substr($type, 3);
            return $this->matchNestedClause($core, $code);
        }
        $rx = $this->clauses[$type] ?? '//';
        preg_match_all($rx, $code, $matches, PREG_SET_ORDER);
        return array_map(function ($m) {
            return array_map('trim', $m);
        }, $matches);
    }

    /**
     * Parses and renders a template file.
     *
     * @param string       $file Template path
     * @param array|object $data Data context
     * @return string Rendered output
     */
    public function parse(string $file, array|object $data = []): string {
        $data = (array) $data;
        $data = json_encode($data);
        $data = json_decode($data);
        $file = realpath($file);
        $this->id_context =
            'd' . substr(sha1($file), 0, 7);
        $file = $this->load($file, $data);
        extract((array) $data, EXTR_SKIP);
        // Keep original objects available for templates; only top-level needs array access
        ${$this->id_context} = (array) $data;
        ob_start();
        include $file;
        return ob_get_clean();
    }

    /**
     * Loads the compiled template file from cache or recompiles it.
     *
     * Uses two-stage caching:
     * - .expanded.html: template with all includes resolved
     * - .php: fully compiled PHP template
     *
     * @param string       $file Absolute path to the template file
     * @param array|object $data Contextual data available to the compiler
     * @return string Path to the compiled PHP file
     */
    private function load(string $file, array|object $data): string {
        $blocks = [];
        $cdir = rtrim($this->cache_dir, '/');
        $cbase = str_replace('/', '_', $file);
        $cfile = "$cdir/$cbase.php";
        $tfile = "$cdir/$cbase.tpl";
        $needs_rebuild = (
            !$this->cache_enabled ||
            !file_exists($cfile) ||
            !file_exists($tfile) ||
            filemtime($cfile) < filemtime($file) ||
            filemtime($tfile) < filemtime($file));
        if ($needs_rebuild) {
            $code = file_get_contents($file);
            $expanded = $this->expandIncludes($code, $data, $file);
            file_put_contents($tfile, $expanded);
        } else {
            $expanded = file_get_contents($tfile);
        }
        if ($needs_rebuild) {
            $compiled = $this->compile($expanded, $data, $blocks, $file);
            file_put_contents($cfile, $compiled);
        }
        return $cfile;
    }

    /**
     * Resolves all @include clauses recursively before compilation.
     *
     * @param string       $code Template code
     * @param array|object $data Template variables
     * @param string       $file Current file path for error messages
     * @return string Template code with all includes inlined
     * @throws \RuntimeException If any include file is missing
     */
    private function expandIncludes(string $code, array|object $data, string $file): string {
        $matches = $this->matchClause('include', $code);
        foreach ($matches as $tag) {
            $expr = $tag[2];
            $clau = strtolower($tag[1]);
            $ifile = $this->evald($expr, $data);
            if (!file_exists($ifile)) {
                throw new \RuntimeException("<b>Template error:</b> Include \"$ifile\" not found in <b>$file</b>");
            }
            $cont = file_get_contents($ifile);
            $cont = $this->expandIncludes($cont, $data, $ifile);
            $cont = $this->trace($clau, $ifile, $cont);
            $code = str_replace($tag[0], $cont, $code);
        }
        return $code;
    }

    /**
     * Compiles template code into PHP.
     * Skips @include clauses (already resolved in expandIncludes).
     *
     * @param string       $code   Template source code
     * @param array|object $data   Template variables
     * @param array        $blocks Block definitions for scoped context
     * @return string PHP code
     */
    private function compile(string $code, array|object $data, array &$blocks, string $file = '', array $stack = [], ?string $parent_id = null): string {
        $defs = [];
        foreach (array_keys($this->clauses) as $k) {
            if (str_starts_with($k, 'set')) {
                array_unshift($defs, $k);
            } else {
                $defs[] = $k;
            }
        }
        foreach ($defs as $name) {
            if (str_starts_with($name, 'end')) {
                continue;
            }
            foreach ($this->matchClause($name, $code) as $m) {
                if ($m[1] === 'include') {
                    continue;
                }
                $mthd = "compile" . ucfirst($m[1]);
                if (method_exists($this, $mthd)) {
                    $new = $this->$mthd($code, $data, $blocks, $m, $file, $stack, $parent_id);
                    return $this->compile($new, $data, $blocks, $file, $stack, $parent_id);
                }
            }
        }
        $code = $this->compileBlockRef($code, $stack, $blocks);
        $code = $this->compilePhp($code);
        $code = $this->compileEcho($code);
        return $code;
    }

    /**
     * Compiles an @include clause.
     * Throws if the target file does not exist.
     *
     * @param string       $code   Current template code
     * @param array|object $data   Template data
     * @param array        $blocks Block context (unused here)
     * @param array        $tag    Clause tokens
     * @return string Modified code with raw include content
     * @throws \RuntimeException When the include file is missing
     */
    private function compileInclude(string $code, array|object $data, array &$blocks, array $tag, string $file): string {
        $expr = $tag[2];
        $clau = strtolower($tag[1]);
        $ifile = $this->evald($expr, $data);
        try {
            if (!file_exists($ifile)) {
                throw new \RuntimeException("<b>Template error:</b> Include \"$ifile\" not found in <b>$file</b>");
            }
        } catch (\RuntimeException $e) {
            return $e->getMessage();
        }
        $cont = file_get_contents($ifile);
        $cont = $this->trace($clau, $ifile, $cont);
        return str_replace($tag[0], $cont, $code);
    }

    /**
     * Compiles a @setblock clause and stores it in scoped context.
     * Supports {{@parent}} placeholder for inheritance.
     *
     * @param string       $code   Template code
     * @param array|object $data   Template data
     * @param array        $blocks Scoped block context
     * @param array        $tag    Clause tokens
     * @return string Code with clause removed
     */
    private function compileSetblock(string $code, array|object $data, array &$blocks, array $tag, string $file = '', array $stack = []): string {
        $name = $tag[2] ?? '';
        $content = $tag[3] ?? '';
        $clau = strtolower($tag[1]);
        $scope = $this->getCurrentBlockPath($stack);
        $path = rtrim($scope, '/') . '/' . ltrim($name, '/');
        $stack[] = trim($name, '/');
        $blocks[$path] = str_replace('{{@parent}}', $blocks[$path] ?? '', $content);
        $this->trace($clau, $path);
        return str_replace($tag[0], '', $code);
    }

    /**
     * Compiles a @block clause using scoped context only.
     * Tracks parent block context and assigns unique ID per invocation.
     *
     * @param string       $code   Template code
     * @param array|object $data   Template data
     * @param array        $blocks Scoped block context
     * @param array        $tag    Clause tokens
     * @return string Code with compiled block content
     * @throws \RuntimeException If the block is not defined
     */
    private function compileBlock(string $code, array|object $data, array &$blocks, array $tag, string $file = '', array $stack = [], ?string $parent_id = null): string {
        $name = $tag[2] ?? '';
        $expr = $tag[3] ?? '';
        $clau = strtolower($tag[1]);
        $path = $this->resolveBlockPath($blocks, $name, $stack);
        try {
            if (!$path) {
                throw new \RuntimeException("<b>Template error:</b> Block \"$name\" undefined in <b>$file</b>");
            }
        } catch (\RuntimeException $e) {
            return $e->getMessage();
        }
        $block_id = $path . '_' . substr(sha1($path . $expr), 0, 6);
        $stack[] = trim($name, '/');
        $compiled = $this->compile($blocks[$path], $data, $blocks, $file, $stack, $block_id);
        $scoped = $this->scopeBlock($compiled, $expr, $block_id, $parent_id ?? $this->id_context);
        return str_replace($tag[0], $this->trace($clau, $path, $scoped), $code);
    }

    /**
     * Wraps compiled code in an isolated closure with block-aware variable scoping.
     *
     * @param string      $code      Compiled template code to scope
     * @param string      $expr      Variable expression string (e.g., ['c' => $a])
     * @param string|null $block_id  Unique block ID (optional)
     * @param string|null $parent_id Parent block ID or null (optional)
     * @return string PHP closure-wrapped block
     */
    private function scopeBlock(string $code, string $expr = '', ?string $block_id = null, ?string $parent_id = null): string {
        $expr = $this->sanitize($expr);
        $id = $this->id_context;
        $out = [];
        $out[] = "<?php call_user_func(function()";
        $out[] = "use (\$$id) {";
        $out[] = "extract(\$$id, EXTR_SKIP);";
        if ($block_id) {
            $parent = $parent_id ?? $id;
            $base = "\${$id}['$parent'] ?? \${$id}";
            $out[] = "extract($base, EXTR_SKIP);";
            $merge = $expr ?: '[]';
            $out[] = "\${$id}['$block_id'] = array_merge($base, $merge);";
            $out[] = "extract(\${$id}['$block_id'], EXTR_OVERWRITE);";
        } elseif ($expr) {
            $out[] = "extract($expr, EXTR_OVERWRITE);";
        }
        $out[] = "?>$code<?php }); ?>";
        return implode(" \n", $out);
    }

    /**
     * Replaces all inline block references with scoped closures.
     *
     * @param string $code   Template source code
     * @param array  $stack  Current block stack
     * @param array  $blocks All defined blocks
     * @return string Code with block references compiled
     */
    private function compileBlockRef(string $code, array $stack, array &$blocks): string {
        $offset = 0;
        while (($at = strpos($code, '@', $offset)) !== false) {
            if (!preg_match('/@([a-zA-Z_][a-zA-Z0-9_]*)/', $code, $m, 0, $at)) {
                $offset = $at + 1;
                continue;
            }
            $name = $m[1];
            $name_pos = $at + 1;
            [$full, $args] = $this->extractBlockRef($code, $at, $name_pos);
            if (!$full) {
                $offset = $at + 1;
                continue;
            }
            $path = $this->resolveBlockPath($blocks, $name, $stack);
            if (!$path) {
                $offset = $at + strlen($full);
                continue;
            }
            $args = $this->sanitize($args);
            $repl = $this->scopeBlockRef($path, 'block', $args);
            $code = substr_replace($code, $repl, $at, strlen($full));
            $offset = $at + strlen($repl);
        }
        return $code;
    }

    /**
     * Extracts array Block ref expressions and its args.
     *
     * @param string  $code  Full template code
     * @param integer $start Offset where match starts (used to extract full)
     * @param integer $name  Position where block starts (e.g., @foo)
     * @return array [$full, $args]
     */
    private function extractBlockRef(string $code, int $start, int $name): array {
        $block = '';
        $i = $name;
        $len = strlen($code);
        while ($i < $len && preg_match('/[a-zA-Z0-9_]/', $code[$i])) {
            $block .= $code[$i];
            $i++;
        }
        $after = ltrim(substr($code, $i));
        if (!str_starts_with($after, '[')) {
            return [substr($code, $start, $i - $start), '[]'];
        }
        $open = strpos($code, '[', $i);
        if ($open === false) {
            return [substr($code, $start, $i - $start), '[]'];
        }
        $depth = 0;
        for ($j = $open; $j < $len; $j++) {
            $ch = $code[$j];
            if ($ch === '[') {
                $depth++;
            } elseif ($ch === ']') {
                $depth--;
            }
            if ($depth === 0) {
                break;
            }
        }
        if ($depth !== 0) {
            return [substr($code, $start, $j - $start + 1), '[]'];
        }
        $close = $j;
        $full = substr($code, $start, $close - $start + 1);
        $args = substr($code, $open, $close - $open + 1);
        return [$full, $args];
    }

    /**
     * Generates an inline block reference expression with scoped context.
     *
     * Used in compileBlockRef to inject a block call as a closure expression.
     * Wraps the block call with its own scoped variable context using $argsPhp.
     *
     * @param string $path    Resolved block path
     * @param string $key     Parameter key used in the parent block
     * @param string $argsPhp Argument array expression (already sanitized)
     * @return string PHP expression that renders the block output
     */
    private function scopeBlockRef(string $path, string $key, string $argsPhp): string {
        return ($key !== 'block' ? "'$key' => " : '') . "(function() use (\${$this->id_context}) {
        ob_start(); extract(\${$this->id_context}, EXTR_SKIP);
        \$__data = array_merge(\${$this->id_context}, $argsPhp);
        extract(\$__data, EXTR_OVERWRITE);
        ?>{{@block $path $argsPhp}}<?php return ob_get_clean(); })()";
    }

    /**
     * Compiles a @var clause and injects it into the scoped block context.
     *
     * @param string       $code   Template code
     * @param array|object $data   Template data
     * @param array        $blocks Block context
     * @param array        $tag    Clause tokens
     * @return string Code with variable assignment injected
     */
    private function compileVar(string $code, array|object $data, array &$blocks, array $tag, string $file = '', array $stack = [], ?string $parent_id = null): string {
        $name = $tag[2] ?? '';
        $expr = $tag[3] ?? "''";
        $block_id = $parent_id ??
            $this->id_context;
        $id = $this->id_context;
        $value = $this->sanitize($expr);
        $line = "<?php \$$name = $value; \${$id}['$block_id']['$name'] = $$name; ?>";
        return str_replace($tag[0], $line, $code);
    }

    /**
     * Compiles inline PHP statements.
     *
     * @param string $code Template code
     * @return string PHP-transformed code
     */
    private function compilePhp(string $code): string {
        $rx = $this->getClauseNames();
        $rx = implode('|', array_map(function ($c) {
            return "$c\\b";
        }, $rx));
        $rx = "~\{{@\s*(?!(?:$rx))(.+?)\s*}}~is";
        return preg_replace_callback($rx, function ($m) {
            $expr = $this->sanitize($m[1]);
            return "<?php $expr ?>";
        }, $code);
    }

    /**
     * Compiles echo expressions.
     *
     * @param string $code Template code
     * @return string Code with echo statements
     */
    private function compileEcho(string $code): string {
        $rx = $this->getClauseNames();
        $rx = implode('|', array_map(function ($c) {
            return "$c\\b";
        }, $rx));
        $rx = "~\{{\s*(?!@?(?:$rx))(.+?)\s*}}~is";
        return preg_replace_callback($rx, function ($m) {
            $expr = trim($m[1]);
            if (str_starts_with($expr, '@')) {
                $expr = $this->sanitize(substr($expr, 1));
                return "<?php echo $expr ?>";
            }
            $expr = $this->sanitize($expr);
            $parts = explode('.', $expr);
            $base = '$' . array_shift($parts);
            foreach ($parts as $p) {
                if ($p === '') {
                    continue;
                }
                $base .= ctype_digit($p) ? '[' . $p . ']' : '->' . $p;
            }
            return "<?php echo $base ?>";
        }, $code);
    }

    /**
     * Matches all nested clause blocks of the form {{@setX}}...{{@endsetX}}.
     *
     * @param string $type Clause name
     * @param string $code Template source code
     * @return array Matched nested clause blocks
     */
    private function matchNestedClause(string $type, string $code): array {
        $offset = 0;
        $matches = [];
        $rx_open = '/{{@\s*set' . $type . '\b(.*?)}}/i';
        $rx_tag = '/{{@\s*(set' . $type . '\b.*?|endset' . $type . ')\s*}}/i';
        while ($open = $this->findOpenTag($code, $rx_open, $offset)) {
            [$start, $name, $open_len] = $open;
            $close = $this->findEndTag($code, $rx_tag, $offset);
            if (!$close) {
                break;
            }
            [$tag_len, $depth] = $close;
            if ($depth !== 0) {
                break;
            }
            $close_len = $tag_len;
            $inner = substr($code, $start + $open_len, $offset - $start - $open_len - $close_len);
            $full = substr($code, $start, $offset - $start);
            $matches[] = [$full, 'set' . $type, $name, $inner];
        }
        return $matches;
    }

    /**
     * Finds the next opening clause tag.
     *
     * @param string  $code    Template source code
     * @param string  $rx_open Regex for opening clause
     * @param integer &$offset Offset for scanning (updated)
     * @return array|null Match tuple or null if not found
     */
    private function findOpenTag(string $code, string $rx_open, int &$offset): ?array {
        if (!preg_match($rx_open, $code, $open, PREG_OFFSET_CAPTURE, $offset)) {
            return null;
        }
        $start = $open[0][1];
        $name = trim($open[1][0]);
        $open_len = strlen($open[0][0]);
        $offset = $start + $open_len;
        return [$start, $name, $open_len];
    }

    /**
     * Finds the matching end clause tag.
     *
     * @param string  $code    Template source code
     * @param string  $rx_tag  Regex for both open/close tags
     * @param integer &$offset Offset for scanning (updated)
     * @return array|null Tuple [tag_length, depth] or null
     */
    private function findEndTag(string $code, string $rx_tag, int &$offset): ?array {
        $depth = 1;
        while ($depth > 0 && preg_match($rx_tag, $code, $tag, PREG_OFFSET_CAPTURE, $offset)) {
            $tag_name = strtolower(trim($tag[1][0]));
            $tag_len = strlen($tag[0][0]);
            $offset = $tag[0][1] + $tag_len;
            $depth += str_starts_with($tag_name, 'set') ? 1 : -1;
        }
        return [$tag_len ?? 0, $depth];
    }

    /**
     * Resolves the block path by traversing scope hierarchy upwards.
     *
     * @param array  $blocks All defined blocks
     * @param string $name   Block name being requested
     * @return string|null Fully resolved block path or null if not found
     */
    private function resolveBlockPath(array $blocks, string $name, array $stack): ?string {
        $name = trim($name, '/');
        while (true) {
            $path = array_merge($stack, [$name]);
            $path = '/' . implode('/', array_filter($path));
            if (isset($blocks[$path])) {
                return $path;
            }
            if (empty($stack)) {
                break;
            }
            array_pop($stack);
        }
        return null;
    }

    /**
     * Gets the current block path.
     *
     * @return string Current block path
     */
    private function getCurrentBlockPath(array $stack): string {
        $parts = array_filter($stack);
        return '/' . implode('/', $parts);
    }

    /**
     * Removes unsafe expressions.
     *
     * @param string $expr Raw expression
     * @return string Sanitized expression
     */
    private function sanitize(string $expr): string {
        $rx = '/<\?(php|=)?|{{@/';
        $unsafe = preg_match($rx, $expr);
        if ($unsafe) {
            return '';
        }
        return $expr;
    }

    /**
     * Evaluates a PHP expression within a given context.
     *
     * @param string       $expr PHP expression
     * @param array|object $data Variables context
     * @return mixed Evaluated result
     */
    private function evald(string $expr, array|object $data) {
        $res = '';
        $expr = $this->sanitize($expr);
        extract((array) $data, EXTR_SKIP);
        try {
            $res = eval("return $expr;");
        } catch (\Throwable $e) {
            $res = '';
        }
        return $res;
    }

    /**
     * Recursively converts stdClass to array.
     *
     * @param mixed $item
     * @return mixed
     */
    private function toArray(mixed $item): mixed {
        if (is_object($item)) {
            return $this->toArray((array) $item);
        }
        if (is_array($item)) {
            foreach ($item as $k => $v) {
                $item[$k] = $this->toArray($v);
            }
        }
        return $item;
    }

    /**
     * Adds an HTML comment trace for debugging purposes.
     *
     * @param string $name Clause or trace type (e.g., 'include', 'block')
     * @param string $desc Description or filename for trace context
     * @param string $next Optional content to append after the trace
     * @return string Trace comment followed by the given content
     */
    private function trace(string $name, string $desc, string $next = ''): string {
        $trace = '';
        $this->trace_files &&
            $trace = "<!-- @$name: $desc -->\n";
        return $trace . $next;
    }
}
