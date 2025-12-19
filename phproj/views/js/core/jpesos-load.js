/**
 * jPesosLoadModule
 * =================
 * Adds `.load(url, selector)` to jPesos for HTML elements.
 * Loads remote HTML, optionally filters it with a selector and injects it.
 *
 * Events fired on the element:
 * - loadstart
 * - loadprocess   (reserved, not used internally)
 * - loadsuccess   (alias: loadcomplete)
 * - loaderror
 * - loadend
 *
 * Usage:
 * j$('#container').load('/page.html');
 * j$('#container').load('/page.html', '#section');
 */

function jPesosLoadModule(out, j$) {
    if (out.type !== 'html') return;
    out.load = function(url, selector) {
        out.trigger('loadstart');
        fetch(url).then(function(response) {
            if (!response.ok) throw new Error('Network error');
            return response.text();
        }).then(function(html) {
            out.empty();
            var $temp = j$.$(document).tmp();
            $temp.html(html);
            var $content = selector ? $temp.find(selector) : $temp;
            html = $content.getOuterHtml();
            out.html(html); $temp.del();
            out.trigger('loadcomplete', html);
        }).catch(function(err) {
            out.trigger('loaderror', err);
            console.error('j$.load error:', err);
        }).finally(function() {
            out.trigger('loadend');
        });
        return out;
    };
}

/* Classic and Module export */
if (typeof window !== 'undefined') {
    window.jPesosLoadModule =
    window.jPesosLoadModule ||
    jPesosLoadModule;
} export default jPesosLoadModule;
