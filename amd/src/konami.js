/* jshint ignore:start */
define(['jquery', 'core/log'], function($, log) {

    "use strict"; // jshint ;_;

    log.debug('Squared Konami AMD');

    var callback = function() { };
    // [up, up, down, down, left, right, left, right, b, a];
    var code = [38, 38, 40, 40, 37, 39, 37, 39, 66, 65];
    var i = 0;

    $.fn.konami = function(f) {
        if (typeof f == 'function') {
            callback = f;
        }
        return this.keydown(function(e) {
            /* Increment i if the key pressed matched the next key in the sequence.
               if not, i goes back to zero, so you can start the code again. */
            i = (e.keyCode == code[i]) ? i + 1 : 0;

            // If i reaches the length of the code array, the code must have been entered properly.
            if (i == code.length) {
                typeof callback == 'function' && callback();
                i = 0;
            }
        });
    };
});