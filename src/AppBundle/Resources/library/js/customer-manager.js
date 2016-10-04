function CustomerManager()
{
    var customer = getDefaultCustomerObject();

    EventDispatcher.subscribe('CustomerManager.login.success', function() {
        EventDispatcher.trigger('CustomerManager.customer.loaded');
    });

    EventDispatcher.subscribe('appLoaded', function() {
        this.init();
    }.bind(this));

    this.init = function() {
        this.checkAuth().then(function (response) {
            customer = response.data;
            EventDispatcher.trigger('CustomerManager.customer.loaded');
            EventDispatcher.trigger('CustomerManager.init');
        }, function () {
            Debugger.error('Backed customer retrieval failed.');
        });
    }

    this.reloadCustomer = function() {
        var promise = this.checkAuth();
        promise.then(function (response) {
            customer = response.data;
        }, function() {
            Debugger.error('Unable to retrieve a customer.');
        });
        return promise;
    }

    this.hasSubscription = function(productId) {
        return -1 !== customer.access.subscriptions.indexOf(productId);
    }

    this.hasActiveSubscription = function(productId) {
        var orders = this.getOrders(productId);
        for (var i = 0; i < orders.length; i++) {
            if ('active' === orders[i].status) {
                return true;
            }
        };
        return false;
    }

    // Return the latest active CUSTOMER order or null
    this.getActiveOrder = function(productId) {
        var orders = this.getOrders(productId);
        for (var i = 0; i < orders.length; i++) {
            if ('active' === orders[i].status) {
                return orders[i];
            }
        };
        return null;
    }

    // Return the latest active GROUP order or null
    this.getActiveGroupOrder = function(productId, groupId) {
        var orders = this.getGroupOrders(productId, groupId);
        for (var i = 0; i < orders.length; i++) {
            if ('active' === orders[i].status) {
                return orders[i];
            }
        };
        return null;
    }

    // return all future orders for the specified GROUP or empty array
    this.getFutureGroupOrders = function(productId, groupId) {
        var out = [];
        var orders = this.getGroupOrders(productId, groupId);
        for (var i = 0; i < orders.length; i++) {
            if ('pending' === orders[i].status) {
                out.push(orders[i]);
            }
        };
        return out;
    }

    // return all future orders for this customer or empty array.
    this.getFutureOrders = function(productId) {
        var out = [];
        var orders = this.getOrders(productId);
        for (var i = 0; i < orders.length; i++) {
            if ('pending' === orders[i].status) {
                out.push(orders[i]);
            }
        };
        return out;
    }

    this.getGroupOrders = function(productId, groupId) {
        var orders = [];
        for (var i = 0; i < customer.access.orders.length; i++) {
            if (productId === customer.access.orders[i].id && groupId === customer.access.orders[i].group) {
                orders.push(customer.access.orders[i]);
            }
        };
        return orders;
    }

    this.getOrders = function(productId) {
        var orders = [];
        for (var i = 0; i < customer.access.orders.length; i++) {
            if (productId === customer.access.orders[i].id && 'Customer' === customer.access.orders[i].type) {
                orders.push(customer.access.orders[i]);
            }
        };
        return orders;
    }

    this.getOrder = function(productId) {
        if (this.hasSubscription(productId)) {
            for (var i = 0; i < customer.access.orders.length; i++) {
                if (productId === customer.access.orders[i].id) {
                    return customer.access.orders[i];
                }
            };
        }
        return false;
    }

    this.socialLogin = function(provider) {
        EventDispatcher.trigger('CustomerManager.login.submit');
        var headers = {
            'X-Auth-Service': 'Auth0',
        }
        auth0.signin(
            {
                popup: true,
                connection: provider
            },
            function (err, profile, id_token, access_token, state) {
                if (null !== err) {
                    Debugger.warn('Social login failed', err);
                    EventDispatcher.trigger('CustomerManager.login.failure', 'There was an error authenticating with ' + provider);
                    EventDispatcher.trigger('form.login.unlock');
                    EventDispatcher.trigger('form.register.unlock');
                } else {
                    headers['Authorization'] = 'Bearer ' + id_token;
                    return login(profile, headers);
                }
            }
        );
    }

    this.generateDatabaseReset = function(parameters) {
        EventDispatcher.trigger('CustomerManager.reset.generate');
        var promise = Ajax.send('/reset/generate', 'POST', parameters);
        promise.then(function(response) {
            // Debugger.info('Generated reset code:', parameters.code);
            EventDispatcher.trigger('form.reset.unlock');
            EventDispatcher.trigger('CustomerManager.reset.generate.success');
        }, function(error) {
            Debugger.warn('Generate failed', error);
            EventDispatcher.trigger('form.reset.unlock');
            EventDispatcher.trigger('CustomerManager.reset.generate.failure');
        });
        return promise;
    }

    this.databaseResetCheck = function(parameters) {
        EventDispatcher.trigger('CustomerManager.reset.check');
        var promise = Ajax.send('/reset/check', 'POST', parameters);
        promise.then(function(response) {
            Debugger.info('Check passed');
            EventDispatcher.trigger('form.reset.unlock');
            EventDispatcher.trigger('CustomerManager.reset.check.success');
        }, function(error) {
            Debugger.warn('Check failed', error);
            EventDispatcher.trigger('form.reset.unlock');
            EventDispatcher.trigger('CustomerManager.reset.check.failure', [error]);
        });
        return promise;
    }

    this.databaseReset = function(parameters) {
        EventDispatcher.trigger('CustomerManager.reset.submit');
        var promise = Ajax.send('/reset', 'POST', parameters);
        promise.then(function(response) {
            Debugger.info('Reset code matched, proceed to reset pw screen.');
            EventDispatcher.trigger('form.reset.unlock');
            EventDispatcher.trigger('CustomerManager.reset.success');
        }, function(error) {
            Debugger.error('Reset failed!', error);
            EventDispatcher.trigger('form.reset.unlock');
            EventDispatcher.trigger('CustomerManager.reset.failure', [error]);
        });
        return promise;
    }

    this.databaseLogin = function(payload) {
        EventDispatcher.trigger('CustomerManager.login.submit');
        return login(payload);
    }

    this.isLoggedIn = function() {
        return (customer._id) ? true : false;
    }

    this.checkAuth = function() {

        var headers;
        if (Callbacks.has('checkAuth')) {
            headers = Callbacks.get('checkAuth')();
        }
        return Ajax.send('/app/auth', 'GET', undefined, headers);
    }

    this.getCustomer = function() {
        return customer;
    }

    this.logout = function() {
        if (this.isLoggedIn()) {

            var promise = Ajax.send('/app/auth/destroy', 'GET');
                promise.then(function (response) {
                // Success
                customer = response.data;
                EventDispatcher.trigger('CustomerManager.logout.success', [response]);
                EventDispatcher.trigger('CustomerManager.customer.unloaded');
            },
            function(jqXHR) {
                // Error
                var errors  = jqXHR.errors|| [{}];
                var error   = errors[0];
                var message = error.detail || 'An unknown error occured.';

                Debugger.warn('Unable to logout customer', errors);
                EventDispatcher.trigger('CustomerManager.logout.failure', [message]);
            });

        } else {
            Debugger.warn('Tried to logout, already logged out.');
        }
    }

    this.databaseRegister = function(payload) {
        EventDispatcher.trigger('CustomerManager.register.submit');
        var promise = Ajax.send('/app/submission/customer-account', 'POST', payload);
        promise.then(function (response) {
            // Success
            customer = response.data;
            EventDispatcher.trigger('CustomerManager.register.success', [response]);
        },
        function(jqXHR) {
            var errors  = jqXHR.errors|| [{}];
            var error   = errors[0];
            var message = error.detail || 'An unknown error occured.';

            Debugger.warn('Unable to register customer', errors);
            EventDispatcher.trigger('CustomerManager.register.failure', [message, jqXHR]);
        });
        return promise;

    }

    function login(payload, headers)
    {
        var promise = Ajax.send('/app/auth', 'POST', payload, headers);
        promise.then(function (response) {
            // Success
            customer = response.data;
            EventDispatcher.trigger('form.login.unlock');
            EventDispatcher.trigger('CustomerManager.login.success', [response, payload]);
        },
        function(jqXHR) {
            // Error
            var errors  = jqXHR.errors|| [{}];
            var error   = errors[0];
            var message = error.detail || 'An unknown error occured.';
            Debugger.warn('Unable to login customer', errors);
            EventDispatcher.trigger('form.login.unlock');
            EventDispatcher.trigger('CustomerManager.login.failure', [message]);
        });
        return promise;
    }

    function getDefaultCustomerObject()
    {
        return {};
    }
}
