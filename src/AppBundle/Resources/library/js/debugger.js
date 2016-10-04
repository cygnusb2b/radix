function Debugger(enabled)
{
    init();

    var enabled = Boolean(enabled) || false;

    this.enable = function() {
        enabled = true;
        return this;
    }

    this.disable = function() {
        enabled = false;
        return this;
    }

    this.log = function() {
        dispatch('log', arguments);
        return this;
    }

    this.info = function() {
        dispatch('info', arguments);
        return this;
    }

    this.warn = function() {
        dispatch('warn', arguments);
        return this;
    }

    this.error = function() {
        dispatch('error', arguments);
        return this;
    }

    /**
     *
     */
    function dispatch(method, passed)
    {
        if (true === enabled || 'error' === method) {
            var args = ['COMPONENTS DEBUGGER:'];
            for (var i = 0; i < passed.length; i++)  {
                var n = i + 1;
                args[n] = passed[i];
            }
            console[method].apply(console, args);
        }
    }

    /**
     *
     */
    function init()
    {
        if (typeof console === 'undefined') {
            console = {};
        }
        var methods = ['log', 'info', 'warn', 'error'];
        for (var i = 0; i < methods.length; i++) {
            var method = methods[i];
            if (typeof console[method] === 'undefined') {
                console[method] = function() {};
            }
        }
    }
}
