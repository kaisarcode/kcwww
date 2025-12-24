/**
 * jPesosDomWatchModule
 * ====================
 * Adds `.watch()` and `.unwatch()` to jPesos for HTML elements.
 * Internally creates a DOMWatch instance per element.
 *
 * Usage:
 * j$('body').watch('form', function(el, type){
 *     console.log(el, type);
 * });
 *
 * j$('body').unwatch('form'); // Remove watchers for selector
 * j$('body').unwatch();       // Remove all watchers
 */

function jPesosDomWatchModule(out, j$) {
    if (out.type !== 'html') return;
    out.watch = function(selector, callback) {
        selector = selector || '*';
        callback = callback || function() {};
        out.each(function(el) {
            if (!el.__j$DOMWatch__) { el.__j$DOMWatch__ = new DOMWatch(el); }
            var watcher = el.__j$DOMWatch__;
            watcher.added(selector, function(e) { callback(e, 'add'); });
            watcher.removed(selector, function(e) { callback(e, 'del'); });
            watcher.attribute(selector, function(e) { callback(e, 'atr'); });
        });
        return out;
    };
    out.unwatch = function(selector) {
        out.each(function(el) {
            var watcher = el.__j$DOMWatch__;
            if (!watcher) return;
            if (selector) { watcher.remove(selector); }
            else { watcher.destroy();
            delete el.__j$DOMWatch__; }
        });
        return out;
    };
}

/* Classic and Module export */
if (typeof window !== 'undefined') {
    window.jPesosDomWatchModule =
    window.jPesosDomWatchModule ||
    jPesosDomWatchModule;
} export default jPesosDomWatchModule;
