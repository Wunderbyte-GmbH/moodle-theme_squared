/* jshint ignore:start */
define(['jquery', 'theme_bootstrap/bootstrap', 'core/log'], function($, bootstrap, log) {

    "use strict"; // jshint ;_;

    log.debug('Squared Boostrap AMD opt in functions');

    return {
        init: function() {

            $(document).ready(function($) {
                $("[data-toggle=tooltip]").tooltip();
                $("[data-toggle=popover]").popover().click(function(e) {
                    e.preventDefault()
                });
            });
            log.debug('Squared Boostrap AMD init');
        }
    }
});
/* jshint ignore:end */