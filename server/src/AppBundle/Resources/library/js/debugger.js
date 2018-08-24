function Debugger(enabled, level)
{
    init();

    var level   = level || 'error';
    var enabled = Boolean(enabled) || false;

    var levels = {
        log   : 0,
        info  : 1,
        warn  : 2,
        error : 3
    };

    this.enable = function() {
        enabled = true;
        return this;
    }

    this.disable = function() {
        enabled = false;
        return this;
    }

    this.isEnabled = function() {
        return enabled;
    }

    this.isValid = function() {
        return -1 !== getLevelIndex();
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

    this.getLevel = function() {
        return level;
    }

    this.setLevel = function(value) {
        level = value;
        return this;
    }

    function getLevelIndex() {
        return levels.hasOwnProperty(level) ? levels[level] : -1;
    }

    /**
     *
     */
    function dispatch(method, passed)
    {
        if (true === enabled && levels[method] >= getLevelIndex()) {
            var args = ['RADIX DEBUGGER:'];
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
