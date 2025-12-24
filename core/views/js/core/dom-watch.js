/**
 * DOMWatch
 * ========
 * Lightweight DOM mutation observer utility.
 * Observes added nodes, removed nodes and attribute changes inside a context.
 *
 * Usage:
 * var w = new DOMWatch(document.body);
 *
 * w.added('div.item', function(el){
 *     console.log('Added:', el);
 * });
 *
 * w.removed('.box', function(el){
 *     console.log('Removed:', el);
 * });
 *
 * w.attribute('[data-x]', function(el){
 *     console.log('Attribute changed:', el);
 * });
 *
 * w.remove('div.item');  // Remove watchers for selector
 * w.destroy();           // Stop observing altogether
 */

function DOMWatch(ctx, opt) {
    ctx = ctx || document.documentElement;
    opt = opt || {
        attributes: true,
        childList: true,
        subtree: true
    };
    var afnc = [];
    var dfnc = [];
    var tfnc = [];
    this.added = function(sl, cb){
        cb = cb || function(){};
        afnc.push({ sl: sl, cb: cb });
    };
    this.removed = function(sl, cb){
        cb = cb || function(){};
        dfnc.push({ sl: sl, cb: cb });
    };
    this.attribute = function(sl, cb){
        cb = cb || function(){};
        tfnc.push({ sl: sl, cb: cb });
    };
    this.destroy = function(){
        ob.disconnect();
    };
    this.remove = function(sl) {
        afnc = afnc.filter(f => f.sl !== sl);
        dfnc = dfnc.filter(f => f.sl !== sl);
        tfnc = tfnc.filter(f => f.sl !== sl);
    };
    var ob = new MutationObserver(function(mutations){
        mutations.forEach(function(r){
            var added = r.addedNodes;
            var remov = r.removedNodes;
            var attrb = r.attributeName;
            Array.from(added).forEach(function(el){
                afnc.forEach(function(f){
                    if (el.nodeType === 1 && el.matches(f.sl)) {
                        f.cb(el);
                    }
                    if (el.querySelectorAll) {
                        el.querySelectorAll(f.sl).forEach(function(child){
                            f.cb(child);
                        });
                    }
                });
            });
            Array.from(remov).forEach(function(el){
                dfnc.forEach(function(f){
                    if (el.nodeType === 1 && el.matches(f.sl)) {
                        f.cb(el);
                    }
                    if (el.querySelectorAll) {
                        el.querySelectorAll(f.sl).forEach(function(child){
                            f.cb(child);
                        });
                    }
                });
            });
            if (attrb) {
                var target = r.target;
                tfnc.forEach(function(f){
                    if (target.matches && target.matches(f.sl)) {
                        f.cb(target);
                    }
                });
            }
        });
    });
    ob.observe(ctx, opt);
    return this;
}

/* Classic and Module export */
if (typeof window !== 'undefined') {
    window.DOMWatch = window.DOMWatch || DOMWatch;
} export default DOMWatch;
