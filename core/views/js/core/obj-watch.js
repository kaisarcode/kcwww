/**
 * OBJWatch
 * ========
 * Observes direct property changes on a plain object.
 * Triggers callbacks whenever a top-level property value changes.
 *
 * Usage:
 * var obj = { a: 1 };
 * var w = OBJWatch(obj);
 * w.bind('log', function(prop, val){ console.log(prop, val); });
 * obj.a = 2; // triggers listener
 *
 * Notes:
 * - Only top-level properties are observed.
 * - Properties receive custom getters/setters.
 * - Re-assigning same value will not trigger.
 */

function OBJWatch(obj) {
    var out = {};
    var owv = '__KCOBJWATCH__';
    var owp = obj[owv];
    if (typeof owp == 'undefined') {
    Object.defineProperty(obj, owv, {
        enumerable: false,
        writable: false,
        value: { listeners: {} }
    });}
    var cbs = obj[owv].listeners;
    out.bind = function(nm, cb){
    cbs[nm] = cb; };
    out.unbind = function(nm){
    if (typeof cbs[nm] !== 'undefined')
    delete cbs[nm]; };
    Object.keys(obj).forEach(function(key){
        var val = obj[key];
        var prop = Object.
        getOwnPropertyDescriptor(obj, key);
        Object.defineProperty(obj, key, {
            get: function(){ return val; },
            set: function(v) {
            if (val !== v) {
                val = v;
                for (var nm in cbs){
                cbs[nm](key, val); }
            }}
        });
    });
    return out;
}

/* Classic and Module export */
if (typeof window !== 'undefined') {
    window.OBJWatch = window.OBJWatch || OBJWatch;
} export default OBJWatch;
