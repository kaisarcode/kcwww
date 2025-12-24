/**
 * JPesos
 * ======
 * A minimalistic DOM manipulation library similar in spirit to jQuery, with optional support
 * for selector prefixing and modular extensions.
 *
 * Features:
 * ---------
 * - Element selection with support for string selectors, nodes, arrays, events, objects, etc.
 * - Chainable methods for DOM manipulation: adding/removing elements, attributes, CSS, events.
 * - Prefixed selectors (useful for scoped styles or class naming conventions).
 * - Plugin/module architecture via `.use()` to extend functionality.
 * - Lightweight and dependency-free.
 *
 * Usage:
 * ------
 * const jpesos = jPesos();     // Initialize
 * const j$ = jpesos.$;         // Alias for usage
 *
 * j$('.container').add('div').addClass('box');
 * j$('button').on('click', function(e, el) {
 *     console.log('Clicked', el);
 * });
 *
 * With prefix:
 * const jpx = jPesos({ pfx: 'app', pfxr: '$x' });
 * jpx.$('$xbutton').addClass('primary');  // Selects elements with class="app-button"
 *
 * Extend with modules:
 * jpesos.use(function(out, j$) {
 *     if (out.type === 'html') {
 *         out.highlight = function() {
 *             out.css({ backgroundColor: 'yellow' });
 *             return out;
 *         };
 *     }
 * });
 *
 * j$('.note').highlight();
 *
 * Methods:
 * --------
 * j$.find(selector, parent) — Main selector method
 * j$.$() — Alias for j$.find
 * j$.use(fn) — Register a module
 *
 * Returned objects support methods like:
 * .add(tag), .del(), .html(), .text(), .attr(), .css()
 * .on(event, callback), .off(event), .trigger(event)
 * .addClass(), .delClass(), .parent(), .next(), .prev()
 *
 * Configuration Options:
 * ----------------------
 * - pfx: String to prefix class names (e.g., 'app')
 * - pfxr: Replacer keyword in selectors (e.g., '$x')
 * - sep: Class name separator (default is '-')
 *
 * License:
 * --------
 * This software is licensed under the GNU General Public License v3.0.
 * You may redistribute and/or modify it under the terms of the GNU GPL.
 *
 * Author:
 * -------
 * KaisarCode <kaisar@kaisarcode.com>
 * Website: https://kaisarcode.com
 */

function jPesos(opts) {

    var j$ = {};

    // Modules
    var modules = [];
    j$.use = function(fn) {
        if (typeof fn == 'function') {
            modules.push(fn);
            fn(j$);
        }
    };

    // Is datatype
    var is = {};
    is.def = function(v){
        return typeof v !== 'undefined';
    };
    is.obj = function(v){
        return typeof v == 'object';
    };
    is.arr = function(v){
        return Array.isArray(v);
    };
    is.str = function(v){
        return typeof v == 'string';
    };
    is.num = function(v) {
        return !isNaN(v);
    };
    is.fun = function(v) {
        return typeof v == 'function';
    };
    is.rgx = function(v) {
        return v instanceof RegExp;
    };

    // Replace in string
    function rplc(srch, repl, str, flag) {
        srch = srch.replace(/[.*+?$^{}()|[\]\\]/g, '\\$&');
        flag = flag || 'gim';
        var rx = new RegExp(srch, flag);
        return str.replace(rx, repl);
    };

    // Config
    if (is.str(opts)) {
    var pfx = opts;
    opts = {};
    opts.pfx = pfx; }
    opts = opts || {};
    // Prefix
    opts.pfx = opts.pfx || '';
    // Prefix replacer
    opts.pfxr = opts.pfxr || '$x';
    // CSS class separator
    if (!is.def(opts.sep)) opts.sep = '-';
    // Expose opts
    j$.opts = opts;

    // Find (or select)
    j$.find = function(s, p){
        var out = {};
        out.length = 0;
        var x = opts.pfx;
        var pfxr = opts.pfxr;
        var p = p || document;
        var ts = typeof s;

        // If selector is undefined
        if (ts == 'undefined') {
            out.type = ts;
            out.length = 0;
            out.type = ts;
        } else

        // If selector is null
        if (s == null) {
            out[0] = s;
            out.length = 1;
            out.type = 'object';
        } else

        // If selector is regexp
        if (s instanceof RegExp) {
            out[0] = s;
            out.length = 1;
            out.type = 'regexp';
        } else {

            // If already a j$ object, unwrap based on its type
            if (s.isj$) {
                for (var i = 0; i < s.length; i++) {
                    out[out.length] = s[i];
                    out.length++;
                }
                out.type = s.type;
            } else

            // If selector is an array
            if (is.arr(s)) {
                s.forEach(function(l){
                    out[out.length] = l;
                    out.length++;
                }); out.type = 'array';
            } else

            // If selector is a Node list
            if (NodeList.prototype.isPrototypeOf(s)) {
                s.forEach(function(l){
                    out[out.length] = l;
                    out.length++;
                }); out.type = 'html';
            } else

            // If selector is event
            if (s instanceof Event) {
                out[0] = s;
                out.length = 1;
                out.type = 'event';
            } else

            // If selector is a html elem
            if (s.tagName || s == document || s == window) {
                out[0] = s;
                out.length = 1;
                out.type = 'html';
            } else

            // If selector is an object
            if (ts == 'object') {
                out[0] = s;
                for (var n in s) { out.length++; }
                out.type = 'object';
            } else

            // If selector is a string
            // treat it as a queryselector
            if (ts == 'string') {
                var tmp = [];
                var pq = j$.find(p);
                var x = opts.pfx;
                var xr = opts.pfxr;
                s = rplc(xr, x, s);
                pq.each(function(q){
                    try {
                    var rs = q.querySelectorAll(s);
                    rs.forEach(function(l){
                        if (tmp.indexOf(l) === -1) tmp.push(l);
                    }); } catch (err) {};
                });
                tmp.forEach(function(l){
                    out[out.length] = l;
                    out.length++;
                }); out.type = 'html';
            }

            // If selector is anything else
            // fill output with the input var
            else {
                out[0] = s;
                out.length = 1;
                out.type = ts;
            }
        }

        // Denote is a j$ collection
        if (s !== null) {
            out.isj$ = true;
        }

        // Iterate selected elems
        if (ts !== 'undefined') {
            out.each = function(cb) {
                for (var i = 0; i < out.length; i++) {
                    cb(out[i], i);
                }
            };
        }

        // Select element from out
        if (ts !== 'undefined') {
            out.eq = function(i) {
                return j$.find(out[i]);
            };
        }

        // Select elems in out
        if (out.type == 'html') {
            out.find = function(s) {
                var p = [];
                var x = opts.pfx;
                var l = out.length;
                for (var i = 0; i < l; i++) {
                    p.push(out[i]);
                } p.type = 'html';
                return j$.find(s, p, x);
            };
        }

        // Add element
        if (out.type == 'html') {
            out.add = function(tag, before) {
                before = before || false;
                var o = [];
                tag = tag || 'div';
                out.each(function(p){
                    if (!is.def(tag.nodeName)) {
                        var el = document.createElement(tag);
                    } else { var el = tag; }
                    !before ? p.appendChild(el):
                    p.insertBefore(el, p.childNodes[0]);
                    o.push(el);
                });
                o.type = 'html';
                o.isj$ = true;
                return j$.find(o);
            };
        }

        // Add virtual element
        if (out.type == 'html') {
            out.tmp = function(tag) {
                tag = tag || 'div';
                var el = document.createElement(tag);
                return j$.find(el);
            };
        }

        // Delete element
        if (out.type == 'html') {
            out.del = function() {
                out.each(function(el){
                    try {
                        el.parentNode.removeChild(el);
                    } catch (err) {};
                }); return out;
            };
        }

        // Copy element
        if (out.type == 'html') {
            out.copy = function() {
                var clones = [];
                out.each(function(el) {
                    clones.push(el.cloneNode(true));
                });
                return j$.find(clones);
            };
        }

        // Move elements to another target
        // @target: j$ element or DOM node
        if (out.type == 'html') {
            out.move = function(target) {
                var dest = j$.find(target);
                if (dest.type !== 'html' || !dest.length) return out;
                out.each(function(el) {
                    dest[0].appendChild(el);
                });
                return out;
            };
        }

        // Get parent
        if (out.type == 'html') {
            out.parent = function() {
                var o = [];
                out.each(function(el){
                    var tkn = false;
                    var p = el.parentNode;
                    o.forEach(function(t){
                        if (t === p) tkn = true;
                    }); if (!tkn && p) o.push(p);
                });
                o.type = 'html';
                o.isj$ = true;
                return j$.find(o);
            };
        }

        // Get previous sibling
        if (out.type == 'html') {
            out.prev = function() {
                var o = [];
                out.each(function(el){
                    var tkn = false;
                    var p = el.previousElementSibling;
                    o.forEach(function(t){
                        if (t === p) tkn = true;
                    }); if (!tkn && p) o.push(p);
                });
                o.type = 'html';
                o.isj$ = true;
                return j$.find(o);
            };
        }

        // Get next sibling
        if (out.type == 'html') {
            out.next = function() {
                var o = [];
                out.each(function(el){
                    var tkn = false;
                    var p = el.nextElementSibling;
                    o.forEach(function(t){
                        if (t === p) tkn = true;
                    }); if (!tkn && p) o.push(p);
                });
                o.type = 'html';
                o.isj$ = true;
                return j$.find(o);
            };
        }

        // Get HTML content
        if (out.type == 'html') {
            out.getHtml = function() {
                if (!out.length) return undefined;
                return out[0].innerHTML;
            };
        }

        // Get outer HTML content
        if (out.type == 'html') {
            out.getOuterHtml = function() {
                if (!out.length) return undefined;
                return out[0].outerHTML;
            };
        }

        // Add HTML to element
        if (out.type == 'html') {
            out.addHtml = function(htm, pfx) {
                if (htm !== 0) htm = htm || '';
                htm = htm.toString();

                // Replace prefix
                if (!is.def(pfx)) pfx = x;
                htm = rplc('\\'+pfxr, '$j$PFXR', htm);
                htm = rplc(pfxr, pfx, htm);
                htm = rplc('$j$PFXR', pfxr, htm);

                // Add contents
                out.each(function(el){
                    el.innerHTML += htm;
                }); return out;
            };
        }

        // Empty HTML of element
        if (out.type == 'html') {
            out.delHTML =
            out.empty = function() {
                out.each(function(el){
                    el.innerHTML = '';
                }); return out;
            };
        }

        // Get text content
        if (out.type == 'html') {
            out.getText = function() {
                if (!out.length) return undefined;
                return out[0].textContent;
            };
        }

        // Add text to element
        if (out.type == 'html') {
            out.addText = function(txt) {
                if (txt !== 0) txt = txt || '';
                txt = txt.toString();
                out.each(function(el){
                    el.textContent += txt;
                }); return out;
            };
        }

        // Delete text content
        if (out.type == 'html') {
            out.delText = function() {
                out.each(function(el){
                    el.textContent = '';
                });
                return out;
            };
        }

        // Get Attribute
        // @atr: string attribute name
        if (out.type == 'html') {
            out.getAttr = function(atr) {
                if (!out.length) return undefined;
                return out[0].getAttribute(atr);
            };
        }

        // Add attributes
        // @atr: string or object with keys:values
        // @val: value (if @attr is string)
        if (out.type == 'html') {
            out.addAttr = function(atr, val) {
                atr = atr || {};
                out.each(function(el){
                    if (is.str(atr)) {
                        el.setAttribute(atr, val);
                    } else {
                        for (var k in atr) {
                            el.setAttribute(k, atr[k]);
                        }
                    }
                }); return out;
            };
        }

        // Del attributes
        // Each argument passed is an attribute name
        if (out.type == 'html') {
            out.delAttr = function() {
                var atr = arguments;
                out.each(function(el){
                    for (var i = 0; i < atr.length; i++) {
                        el.removeAttribute(atr[i]);
                    }
                }); return out;
            };
        }

        // Get or set value
        if (out.type == 'html') {
            out.val = function(v) {
                if (!is.def(v)) {
                    if (!out.length) return undefined;
                    return out[0].value;
                }
                out.each(function(el){
                    el.value = v;
                });
                return out;
            };
        }

        // Get or set DOM properties
        if (out.type == 'html') {
            out.prop = function(prop, val) {
                if (!is.def(val)) {
                    if (!out.length) return undefined;
                    return out[0][prop];
                }
                out.each(function(el){
                    el[prop] = val;
                });
                return out;
            };
        }

        // Get CSS classes (as array, without prefix)
        if (out.type == 'html') {
            out.getClass = function() {
                if (!out.length) return undefined;
                var list = [];
                var cls = out[0].classList;
                cls.forEach(function(c){
                    list.push(c);
                });
                return list;
            };
        }

        // Check if element has a given class
        if (out.type == 'html') {
            out.hasClass = function(cls) {
                if (!out.length || !cls) return false;
                var clss = out.getClass();
                return clss.indexOf(cls) !== -1;
            };
        }

        // Add CSS classes
        // @cls: string with classes
        // @pfx: prefix classes
        if (out.type == 'html') {
            out.addClass = function(cls, pfx) {
                if (!is.def(pfx)) pfx = x;
                if (pfx) pfx += opts.sep;
                cls = cls || '';
                if (is.arr(cls)) {
                    cls = cls.join(' ');
                }
                cls = cls.replace('\n', ' ');
                cls = cls.replace(/\s+/g,' ');
                cls = cls.split(' ');
                out.each(function(el){
                    cls.forEach(function(cls){
                        cls = cls.trim();
                        if (cls) el.classList.add(pfx+cls);
                    });

                    // Remove empty class attr
                    if (
                    !el.classList.length &&
                    is.def(el.attributes['class'])
                    ) { el.removeAttribute('class'); }

                }); return out;
            };
        }

        // Delete CSS classes
        // @cls: string with classes
        // @pfx: prefix classes
        if (out.type == 'html') {
            out.delClass = function(cls, pfx) {
                if (!is.def(pfx)) pfx = x;
                if (pfx) pfx += opts.sep;
                cls = cls || '';
                if (is.arr(cls)) {
                    cls = cls.join(' ');
                }
                cls = cls.replace('\n', ' ');
                cls = cls.replace(/\s+/g,' ');
                cls = cls.split(' ');
                out.each(function(el){
                    cls.forEach(function(cls){
                        cls = cls.trim();
                        if (cls) el.classList.remove(pfx+cls);
                    });

                    // Remove empty class attr
                    if (
                    !el.classList.length &&
                    is.def(el.attributes['class'])
                    ) { el.removeAttribute('class'); }

                }); return out;
            };
        }

        // Prefix CSS classes
        // @pfx: prefix classes
        if (out.type == 'html') {
            out.pfxClass = function(pfx) {
                if (!is.def(pfx)) pfx = x;
                out.each(function(el){
                    var cls = el.classList;
                    var clss = [];
                    cls.forEach(function(cls){
                        cls = cls.replace(pfx+opts.sep, '');
                        clss.push(cls);
                    }); el.className = '';
                    j$.find(el).addClass(clss, pfx);
                }); return out;
            };
        }

        // Get inline CSS style
        // @cls: CSS property name
        if (out.type == 'html') {
            out.getCss = function(cls) {
                if (!out.length) return undefined;
                return out[0].style[cls];
            };
        }

        // Add inline CSS
        // @cls: string or object with keys:values
        // @val: value (if @attr is string)
        if (out.type == 'html') {
            out.addCss = function(cls, val) {
                cls = cls || {};
                out.each(function(el){
                    if (is.str(cls)) {
                        if (cls == 'bgimg') {
                            el.style.backgroundImage =
                            "url('"+val+"')";
                        } else { el.style[cls] = val; }
                    } else {
                        for (var k in cls) {
                            if (k == 'bgimg') {
                                el.style.backgroundImage =
                                "url('"+cls[k]+"')";
                            } else { el.style[k] = cls[k]; }
                        }
                    }
                }); return out;
            };
        }

        // ON event
        // @ev: Event name
        // @fn: Function
        if (out.type == 'html') {
            out.on = function(ev, fn) {
                var doc = document;
                if (is.str(ev)) ev = [ev];
                ev.forEach(function(ev){
                    out.each(function(el){
                        if (!is.def(el.j$EventListeners)) {
                            el.j$EventListeners = {};
                        }
                        if (!is.def(el.j$EventListeners[ev])) {
                            el.j$EventListeners[ev] = [];
                        }
                        var wrapper = function(e){
                            fn(e, el);
                        };
                        el.j$EventListeners[ev].push({fn:fn, wrapper:wrapper});
                        if (el == doc && ev == 'ready') {
                            ev = 'DOMContentLoaded';
                            doc.readyState != 'loading' ?
                            fn(null, el) :
                            el.addEventListener(ev, wrapper);
                        } else {
                            el.addEventListener(ev, wrapper);
                        }
                    });
                }); return out;
            };
        }

        // OFF event
        // @ev: Event name
        // @fn: Function
        if (out.type == 'html') {
            out.off = function(ev, fn) {
                var doc = document;
                if (is.str(ev)) ev = [ev];
                ev.forEach(function(ev){
                    out.each(function(el){
                        if (!is.def(el.j$EventListeners)) {
                            el.j$EventListeners = {};
                        }
                        if (el == doc && ev == 'ready') {
                            ev = 'DOMContentLoaded';
                        }
                        var store = el.j$EventListeners[ev] || [];
                        if (is.def(fn)) {
                            for (var i = store.length - 1; i >= 0; i--) {
                                if (store[i].fn === fn) {
                                    el.removeEventListener(ev, store[i].wrapper);
                                    store.splice(i,1);
                                }
                            }
                        } else {
                            store.forEach(function(rec){
                                el.removeEventListener(ev, rec.wrapper);
                            });
                            el.j$EventListeners[ev] = [];
                        }
                    });
                }); return out;
            };
        }

        // TRIGGER event
        // @ev: Event name
        // @data: Pass data to event
        if (out.type == 'html') {
            out.trigger = function(ev, data) {
                var doc = document;
                data = data || null;
                if (is.str(ev)) ev = [ev];
                ev.forEach(function(ev){
                    out.each(function(el){
                        try {
                            var event = document.createEvent('HTMLEvents');
                            event.initEvent(ev, true, false);
                            event.detail = data;
                        } catch (err) {
                            if (window.CustomEvent && is.fun(window.CustomEvent)) {
                                var event = new CustomEvent(ev, { detail: data });
                            } else {
                                var event = document.createEvent('CustomEvent');
                                event.initCustomEvent(ev, true, true, data);
                            }
                        } el.dispatchEvent(event);
                    });
                }); return out;
            };
        }

        // Stop event bubbling
        if (out.type == 'event') {
            out.stop = function() {
                out.each(function(e){
                    var evt = e ? e:window.event;
                    if (evt.stopPropagation) evt.stopPropagation();
                    if (evt.cancelBubble != null) evt.cancelBubble = true;
                }); return out;
            };
        }

        // Shorthand attr (get or add attribute)
        if (out.type == 'html') {
            out.attr = function(atr, val) {
                return !is.def(val) ? out.getAttr(atr) : out.addAttr(atr, val);
            };
        }

        // Shorthand css (get or add inline style)
        if (out.type == 'html') {
            out.css = function(cls, val) {
                return !is.def(val) ? out.getCss(cls) : out.addCss(cls, val);
            };
        }

        // Shorthand clss (get or add class)
        if (out.type == 'html') {
            out.clss = function(cls, pfx) {
                return !is.def(cls) ? out.getClass() : out.addClass(cls, pfx);
            };
        }

        // Shorthand text (get or add text)
        if (out.type == 'html') {
            out.text = function(txt) {
                return !is.def(txt) ? out.getText() : out.addText(txt);
            };
        }

        // Shorthand html (get or add html)
        if (out.type == 'html') {
            out.html = function(htm, pfx) {
                return !is.def(htm) ? out.getHtml() : out.addHtml(htm, pfx);
            };
        }

        // Apply modules to out
        modules.forEach(function(fn) {
            if (typeof fn == 'function') {
                fn(out, j$);
            }
        });

        // Return output
        return out;

    };

    // jQuery-like wrapper
    j$.$ = j$.find;
    for (var k in j$) {
        j$.$[k] = j$[k];
    }

    // Return
    return j$;
};

/* Classic and Module export */
if (typeof window !== 'undefined') {
    window.jPesos = window.jPesos || jPesos;
} export default jPesos;
