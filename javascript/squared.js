require(['core/first'], function() {
    require(['theme_boost/loader', 'theme_squared/search', 'theme_squared/zoom', 'core/log'], function($, s, z, log) {
        log.debug('Squared JavaScript initialised');
    });
});
