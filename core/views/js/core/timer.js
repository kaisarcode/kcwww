/**
 * timer
 * =====
 * Repeated asynchronous executor with millisecond delay.
 *
 * Usage:
 * var t = timer(500, function(info){
 *     console.log(info.i);
 * });
 *
 * t.stop(); // Stops the interval
 *
 * Returns:
 * - o.id  Interval id
 * - o.i   Execution count
 * - o.stop()  Stop interval
 */

// Wait miliseconsd and exec iteratively
// @t: time in miliseconds
// @cb: callback
/*
Returns an object with
    - "stop" method: Clear interval.
    - "id" property: Interval id.
    - "i" property: Number of times executed.
*/
function timer(t, cb) {
    var o = {};
    o.i = 0;
    o.id = setInterval(
    function(){
        o.i++;
        cb(o);
    },t);
    o.stop = function() {
    clearInterval(o.id);
    }; return o;
};

/* Classic and Module export */
if (typeof window !== 'undefined') {
    window.timer = window.timer || timer;
} export default timer;
