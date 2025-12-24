/**
 * wait
 * ====
 * Single delayed executor in milliseconds.
 *
 * Usage:
 * var w = wait(500, function(info){
 *     console.log(info.i);
 * });
 *
 * w.stop(); // Cancels the timeout
 *
 * Returns:
 * - o.id      Timeout id
 * - o.i       Execution count
 * - o.stop()  Cancel timeout
 */

// Wait miliseconsd and exec
// @t: time in miliseconds
// @cb: callback
/*
Returns an object with
    - "stop" method: Clear interval.
    - "id" property: Interval id.
*/
function wait(t, cb) {
    var o = {};
    o.i = 0;
    o.id = setTimeout(
    function(){
        o.i++;
        cb(o);
    },t);
    o.stop = function() {
    clearInterval(o.id);
    }; return o;
}

/* Classic and Module export */
if (typeof window !== 'undefined') {
    window.wait = window.wait || wait;
} export default wait;
