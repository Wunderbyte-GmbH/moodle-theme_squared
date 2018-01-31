require(['core/first'], function() {
    require(['theme_bootstrap/bootstrap', 'theme_squared/search', 'theme_squared/zoom', 'core/log'], function(bootstrap, s, z, log) {
        log.debug('Squared JavaScript initialised');
    });
});
