require(['core/first'], function() {
    require(['theme_squared/custom', 'core/log'], function(c, log) {
        log.debug('Squared JavaScript initialised');
    });
});
