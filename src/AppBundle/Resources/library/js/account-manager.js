function AccountManager()
{
    var account    = {};
    var identityId = null;

    this.IdentityDetectionCallbacks = [];

    EventDispatcher.subscribe('AccountManager.login.success', function() {
        EventDispatcher.trigger('AccountManager.account.loaded');
    });

    EventDispatcher.subscribe('appLoaded', function() {
        this.init();
    }.bind(this));

    this.init = function() {
        EventDispatcher.trigger('AccountManager.preInit');
        this.checkAuth().then(function (response) {
            account    = response.data;
            identityId = response.identity;

            EventDispatcher.trigger('AccountManager.identity.loaded');

            EventDispatcher.trigger('AccountManager.account.loaded');
            EventDispatcher.trigger('AccountManager.init');
        }, function () {
            Debugger.error('Backed account retrieval failed.');
        });
    }

    this.reloadAccount = function() {
        var promise = this.checkAuth();
        promise.then(function (response) {
            account    = response.data;
            identityId = response.identity;

            EventDispatcher.trigger('AccountManager.identity.loaded');
        }, function() {
            Debugger.error('Unable to retrieve an account.');
        });
        return promise;
    }

    this.databaseLogin = function(payload) {
        EventDispatcher.trigger('AccountManager.login.submit');
        return login(payload);
    }

    this.isLoggedIn = function() {
        return (account._id) ? true : false;
    }

    this.parseDetectionParams = function() {

        var query = Utils.parseQueryString(null, true);
        return (!query.ident) ? null : query.ident;
    }

    this.checkAuth = function() {
        var headers;
        if (Callbacks.has('checkAuth')) {
            headers = Callbacks.get('checkAuth')();
        }

        var detectionParams = this.parseDetectionParams();
        Debugger.log('AccountManager.checkAuth()::detectionParams', detectionParams);

        var url = '/app/auth';
        if (detectionParams) {
            url = url + '?' + $.param({ ident: detectionParams});
        }

        return Ajax.send(url, 'GET', undefined, headers);
    }

    this.getAccount = function() {
        return account;
    }

    this.getIdentityId = function() {
        return identityId;
    }

    this.logout = function() {
        if (this.isLoggedIn()) {

            var promise = Ajax.send('/app/auth/destroy', 'GET');
                promise.then(function (response) {
                // Success
                account    = response.data;
                identityId = response.identity;

                EventDispatcher.trigger('AccountManager.logout.success', [response]);
                EventDispatcher.trigger('AccountManager.account.unloaded');
            },
            function(jqXHR) {
                // Error
                var errors  = jqXHR.errors|| [{}];
                var error   = errors[0];
                var message = error.detail || 'An unknown error occured.';

                Debugger.error('Unable to logout account', errors);
                EventDispatcher.trigger('AccountManager.logout.failure', [message]);
            });
            return promise;
        } else {
            Debugger.warn('Tried to logout, already logged out.');
        }
    }

    this.register = function(payload) {
        EventDispatcher.trigger('AccountManager.register.submit');
        var promise = Ajax.send('/app/submission/identity-account', 'POST', payload);
        promise.then(function (response) {
            // Success
            account = response.data;
            EventDispatcher.trigger('AccountManager.register.success', [response]);
        },
        function(jqXHR) {
            var errors  = jqXHR.errors || [{}];
            var error   = errors[0];
            var message = error.detail || 'An unknown error occured.';

            Debugger.warn('Unable to register account', errors);
            EventDispatcher.trigger('AccountManager.register.failure', [message, jqXHR]);
        });
        return promise;

    }

    function login(payload, headers)
    {
        var promise = Ajax.send('/app/auth', 'POST', payload, headers);
        promise.then(function (response) {
            // Success
            account    = response.data;
            identityId = response.identity;

            EventDispatcher.trigger('AccountManager.login.success', [response, payload]);
        },
        function(jqXHR) {
            // Error
            var errors  = jqXHR.errors|| [{}];
            var error   = errors[0];
            var message = error.detail || 'An unknown error occured.';
            Debugger.warn('Unable to login account', errors);
            EventDispatcher.trigger('AccountManager.login.failure', [message]);
        });
        return promise;
    }
}
