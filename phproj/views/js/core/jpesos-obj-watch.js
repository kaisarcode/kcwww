/**
 * jPesosObjWatchModule
 * ====================
 * Adds .watch() and .unwatch() to jPesos for object-type selections.
 * Uses OBJWatch internally to observe top-level property changes.
 *
 * Usage:
 * j$(obj).watch('log', function(prop, val){ ... });
 * j$(obj).unwatch('log');
 *
 * Applies only when out.type == 'object'.
 */

function jPesosObjWatchModule(out, j$) {
    if (out.type !== 'object') return;
    out.watch = function(name, callback) {
        if (typeof name !== 'string' || typeof callback !== 'function') return out;
        out.each(function(obj) {
        if (!obj.__KCOBJWATCH__) { OBJWatch(obj); }
        obj.__KCOBJWATCH__.bind(name, callback); });
        return out;
    };
    out.unwatch = function(name) {
        out.each(function(obj) {
            var watcher = obj.__KCOBJWATCH__;
            if (!watcher) return;
            if (typeof name === 'string') { watcher.unbind(name); }
            else { Object.keys(watcher).forEach(function(nm) { watcher.unbind(nm); }); }
        });
        return out;
    };
}

/* Classic and Module export */
if (typeof window !== 'undefined') {
    window.jPesosObjWatchModule =
    window.jPesosObjWatchModule ||
    jPesosObjWatchModule;
} export default jPesosObjWatchModule;
