function ClientConfig(config)
{
    config = 'object' === typeof config ? config : {};

    var defaults = {
        debug    : false,
        host     : null,
        appId    : null,
        logLevel : 'info'
    };

    $.extend(defaults, config);
    this.values = defaults;

    if (this.values.debug) {
        Radix.setDebug(this.values.debug);
    }
    Radix.setDebugLevel(this.values.logLevel)

    Debugger.info('Config', this.values);

    this.valid = function() {
        var required = ['host', 'appId'];
        for (var i = 0; i < required.length; i++) {
            var key = required[i];
            if (!defaults[key]) {
                return false;
            }
        }
        return true;
    }
}
