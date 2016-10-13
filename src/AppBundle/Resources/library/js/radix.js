;
(function(Radix, undefined) {
    'use strict';
    var auth0;

    // Private properties

    var Debugger        = new Debugger();
    var Ajax            = new Ajax();
    // @todo Must load server config from the application, via /app/init
    // var ServerConfig    = new ServerConfig(hostname, serverConfig);
    var EventDispatcher = new EventDispatcher();
    var Callbacks       = new Callbacks();
    var Utils           = new Utils();

    var Application = {};
    var Components;
    var Forms;
    var ClientConfig;
    var ComponentLoader;
    var CustomerManager;
    var LibraryLoader;

    Radix.emit = function(key) {
        EventDispatcher.trigger(key);
    };

    Radix.getCustomer = function() {
        return CustomerManager.getCustomer();
    };

    Radix.hasCustomer = function() {
        return CustomerManager.isLoggedIn();
    };

    Radix.init = function(config) {
        ClientConfig = new ClientConfig(config);
        if (true === ClientConfig.valid()) {
            Debugger.info('Configuration initialized and valid.');

            EventDispatcher.subscribe('ready', function() {
                // Get the application, then fire the init event.
                Ajax.send('/app/init', 'GET').then(function(response) {
                    Application = response.data;
                    Debugger.info('Application loaded', Application);
                    EventDispatcher.trigger('appLoaded');
                }, function(jqXHR) {
                    Debugger.error('Unable to load the backend application instance.')
                });
            });

            ComponentLoader = new ComponentLoader();
            CustomerManager = new CustomerManager();
            LibraryLoader   = new LibraryLoader();
        } else {
            Debugger.error('Client config is invalid. Ensure all require properties were set.');
        }
    };

    Radix.on = function(key, callback) {
        EventDispatcher.subscribe(key, callback);
    };

    Radix.registerCallback = function(key, callback) {
        Callbacks.register(key, callback);
    };

    Radix.setDebug = function(bit) {
        bit = Boolean(bit);
        if (true === bit) { Debugger.enable(); } else { Debugger.disable(); }
        return Radix;
    };

    function Callbacks()
    {

        var registered = {};

        this.register = function(key, callback) {
            if ('function' === typeof callback) {
                registered[key] = callback;
            }
        };

        this.get = function (key) {
            if (registered.hasOwnProperty(key)) {
                return registered[key];
            }
            return undefined;
        };

        this.has = function(key) {
            return 'undefined' !== typeof this.get(key);
        };
    }

    /**
     * Standalone components that don't require outside initialization.
     */
    function Components()
    {
        this.ContactSupport    = {{ loadComponent('contact-support') }}
        this.ModalLink         = {{ loadComponent('modal-link') }}
        this.ModalLinkLogin    = {{ loadComponent('modal-link-login') }}
        this.ModalLinkRegister = {{ loadComponent('modal-link-register') }}
        this.CountryPostalCode = {{ loadComponent('form-country-postal-code') }}
        this.FormErrors        = {{ loadComponent('form-errors') }}
        this.FormFieldWrapper  = {{ loadComponent('form-field-wrapper') }}
        this.FormInputHidden   = {{ loadComponent('form-input-hidden') }}
        this.FormInputText     = {{ loadComponent('form-input-text') }}
        this.FormLabel         = {{ loadComponent('form-label') }}
        this.FormLock          = {{ loadComponent('form-lock') }}
        this.FormProductEmail  = {{ loadComponent('form-product-email') }}
        this.FormProductsEmail = {{ loadComponent('form-products-email') }}
        this.FormRadios        = {{ loadComponent('form-radios') }}
        this.FormSelect        = {{ loadComponent('form-select') }}
        this.FormSelectCountry = {{ loadComponent('form-select-country') }}
        this.FormSelectOption  = {{ loadComponent('form-select-option') }}
        this.FormQuestion      = {{ loadComponent('form-question') }}
        this.FormTextArea      = {{ loadComponent('form-textarea') }}
        this.Login             = {{ loadComponent('login') }}
        this.Register          = {{ loadComponent('register') }}
        this.Modal             = {{ loadComponent('modal') }}
        this.ParseQueryString  = {{ loadComponent('parse-query-string') }}
        this.RegisterVerify    = {{ loadComponent('register-verify') }}
        this.ActionVerifyEmail = {{ loadComponent('action-verify-email') }}
        this.ResendVerifyEmail = {{ loadComponent('resend-verify-email') }}

        this.ProductEmailDeployment = {{ loadComponent('product-email-deployment') }}

        this.get = function(name) {
            return this[name];
        }

        this.has = function(name) {
            return 'undefined' !== typeof this.get(name);
        }
    }

    function Forms()
    {
        this.EmailSubscription  = {{ loadForm('email-subscription') }}
        this.Inquiry            = {{ loadForm('inquiry') }}
        this.Register           = {{ loadForm('register') }}
        this.Login              = {{ loadForm('login') }}

        this.get = function(name) {
            return this[name];
        }
    }

    function ComponentLoader()
    {
        EventDispatcher.subscribe('appLoaded', function() {

            Radix.Components    = new Components();
            Radix.Forms         = new Forms();

            Radix.ModalModule               = new ModalModule();
            Radix.ComponentLoader           = new ComponentLoaderModule();

            Radix.FormModule                = new FormModule(); // @deprecated
            Radix.SignIn                    = new SignInComponent();
            Radix.InquiryModule             = new InquiryModule();
            Radix.EmailSubscriptionModule   = new EmailSubscriptionModule();
            Radix.QueryStringModule         = new QueryStringModule();
            Radix.ActionHandlerModule       = new ActionHandlerModule();

            // if (true === ServerConfig.values.comments.enabled) {
                // Radix.Comments = new CommentComponent();
                // Radix.Reviews = new ReviewComponent();
            // }
            // if (true === ServerConfig.values.subscriptions.component.enabled) {
                // Radix.Subscriptions = new SubscriptionsComponent();
            // }
        });

        EventDispatcher.subscribe('CustomerManager.init', function() {
            var componentKeys = ['ModalModule', 'ComponentLoader', 'SignIn', 'InquiryModule', 'EmailSubscriptionModule', 'QueryStringModule', 'ActionHandlerModule']; //, 'Comments', 'Reviews', 'Subscriptions', 'Inquiry'];
            for (var i = 0; i < componentKeys.length; i++) {
                var key = componentKeys[i];
                if (true === Utils.isDefined(Radix[key])) {
                    Radix[key].render();
                }
            }
        });
    }

    function EventDispatcher()
    {
        this.trigger = function(key, parameters) {
            parameters = parameters || [];
            key = createKey(key);
            Debugger.info('Event dispatched', key, parameters);
            $(document).trigger(key, parameters);
        }

        this.subscribe = function(key, callback) {
            key = createKey(key);
            $(document).on(key, callback);
        }

        function createKey(key)
        {
            return 'Radix.' + key;
        }
    }

    function LibraryLoader()
    {
        var count = 0,
            libraries = [
                '//cdnjs.cloudflare.com/ajax/libs/react/0.13.0/react.min.js',
                'http://rsvpjs-builds.s3.amazonaws.com/rsvp-latest.min.js',
                '//checkout.stripe.com/checkout.js',
                '//cdn.auth0.com/w2/auth0-6.js'
            ];

        function loadLibraries() {
            for (var i = 0; i < libraries.length; i++) {
                Debugger.info('Loading library ' + libraries[i]);

                $.ajax({
                    cache: true,
                    url: libraries[i],
                    dataType: 'script'
                }).then(function() {
                    if ('function' === typeof Auth0) {
                        // auth0 = new Auth0({
                        //     domain: ServerConfig.values.external_libraries.auth0.domain,
                        //     clientID: ServerConfig.values.external_libraries.auth0.client_id,
                        //     callbackOnLocationHash: true
                        // });
                    }
                    count = count + 1;
                    if (count >= libraries.length) {
                        EventDispatcher.trigger('ready');
                    }
                }).fail(function() {
                    Debugger.error('Required library could not be loaded!');
                })
            };
        }

        loadLibraries();
    }

    {{ loadModule('modal') }}
    {{ loadModule('component-loader') }}

    {{ loadModule('action-handler') }}
    {{ loadModule('form') }}
    {{ loadModule('sign-in') }}
    {{ loadModule('inquiry') }}
    {{ loadModule('query-string') }}
    {{ loadModule('email-subscription') }}

    {{ loadFile('ajax') }}
    {{ loadFile('client-config') }}
    {{ loadFile('customer-manager') }}
    {{ loadFile('debugger') }}
    {{ loadFile('utils') }}

})(window.Radix = window.Radix || {});
