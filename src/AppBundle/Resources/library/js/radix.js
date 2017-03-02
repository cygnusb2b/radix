{{ loadVendor('react.0.13.min') }}
{{ loadVendor('rsvp.latest.min') }}

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
    var ModuleLoader;
    var AccountManager;
    var LibraryLoader;

    Radix.emit = function(key) {
        EventDispatcher.trigger(key);
    };

    Radix.getAccount = function() {
        return AccountManager.getAccount();
    };

    Radix.getIdentity = function() {
        return AccountManager.getIdentityId();
    };

    Radix.hasAccount = function() {
        return AccountManager.isLoggedIn();
    };

    Radix.addDetectionCallback = function(callback) {
        EventDispatcher.subscribe('AccountManager.preInit', function() {
            AccountManager.IdentityDetectionCallbacks.push(callback);
        });
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

            ModuleLoader   = new ModuleLoader();
            AccountManager = new AccountManager();
            LibraryLoader  = new LibraryLoader();
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

    Radix.setDebugLevel = function(level) {
        Debugger.setLevel(level);
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
        this.AccountBoundLink               = {{ loadComponent('account-bound-link') }}
        this.ContactSupport                 = {{ loadComponent('contact-support') }}
        this.EmailSubscriptions             = {{ loadComponent('email-subscriptions') }}
        this.ModalLink                      = {{ loadComponent('modal-link') }}
        this.ModalLinkLogin                 = {{ loadComponent('modal-link-login') }}
        this.ModalLinkLoginVerbose          = {{ loadComponent('modal-link-login-verbose') }}
        this.ModalLinkRegister              = {{ loadComponent('modal-link-register') }}
        this.ModalLinkResetPasswordGenerate = {{ loadComponent('modal-link-reset-password-generate') }}
        this.CountryPostalCode              = {{ loadComponent('form-country-postal-code') }}
        this.Form                           = {{ loadComponent('form') }}
        this.FormErrors                     = {{ loadComponent('form-errors') }}
        this.FormFieldWrapper               = {{ loadComponent('form-field-wrapper') }}
        this.FormInputHidden                = {{ loadComponent('form-input-hidden') }}
        this.FormInputText                  = {{ loadComponent('form-input-text') }}
        this.FormLabel                      = {{ loadComponent('form-label') }}
        this.FormLock                       = {{ loadComponent('form-lock') }}
        this.FormProductEmail               = {{ loadComponent('form-product-email') }}
        this.FormProductsEmail              = {{ loadComponent('form-products-email') }}
        this.FormRadios                     = {{ loadComponent('form-radios') }}
        this.FormSelect                     = {{ loadComponent('form-select') }}
        this.FormSelectCountry              = {{ loadComponent('form-select-country') }}
        this.FormSelectOption               = {{ loadComponent('form-select-option') }}
        this.FormQuestion                   = {{ loadComponent('form-question') }}
        this.FormTextArea                   = {{ loadComponent('form-textarea') }}
        this.GatedDownload                  = {{ loadComponent('gated-download') }}
        this.Inquiry                        = {{ loadComponent('inquiry') }}
        this.LinkLogout                     = {{ loadComponent('link-logout') }}
        this.Login                          = {{ loadComponent('login') }}
        this.Register                       = {{ loadComponent('register') }}
        this.Modal                          = {{ loadComponent('modal') }}
        this.ParseQueryString               = {{ loadComponent('parse-query-string') }}
        this.RegisterVerify                 = {{ loadComponent('register-verify') }}
        this.ResendVerifyEmail              = {{ loadComponent('resend-verify-email') }}
        this.ResetPassword                  = {{ loadComponent('reset-password') }}
        this.ResetPasswordGenerate          = {{ loadComponent('reset-password-generate') }}
        this.VerifyEmail                    = {{ loadComponent('verify-email') }}
        this.ProductEmailDeployment         = {{ loadComponent('product-email-deployment') }}

        this.get = function(name) {
            return this[name];
        }

        this.has = function(name) {
            return 'undefined' !== typeof this.get(name);
        }
    }

    function Forms()
    {
        this.EmailSubscription      = {{ loadForm('email-subscription') }}
        this.Inquiry                = {{ loadForm('inquiry') }}
        this.Register               = {{ loadForm('register') }}
        this.ResetPasswordGenerate  = {{ loadForm('reset-password-generate') }}
        this.ResetPassword          = {{ loadForm('reset-password') }}
        this.Login                  = {{ loadForm('login') }}
        this.GatedDownload          = {{ loadForm('gated-download') }}

        this.get = function(name) {
            return this[name];
        }
    }

    function ModuleLoader()
    {
        EventDispatcher.subscribe('appLoaded', function() {

            Radix.Components = new Components();
            Radix.Forms      = new Forms();

            Radix.ModalModule     = new ModalModule();
            Radix.ComponentLoader = new ComponentLoaderModule();
        });

        EventDispatcher.subscribe('AccountManager.init', function() {
            var keys = ['ModalModule', 'ComponentLoader'];
            for (var i = 0; i < keys.length; i++) {
                var key = keys[i];
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
        EventDispatcher.trigger('ready');
        // var count = 0,
        //     libraries = [
        //         '//cdnjs.cloudflare.com/ajax/libs/react/0.13.0/react.min.js',
        //         'http://rsvpjs-builds.s3.amazonaws.com/rsvp-latest.min.js'
        //         // '//cdn.auth0.com/w2/auth0-6.js'
        //     ];

        // function loadLibraries() {
        //     for (var i = 0; i < libraries.length; i++) {
        //         Debugger.info('Loading library ' + libraries[i]);

        //         $.ajax({
        //             cache: true,
        //             url: libraries[i],
        //             dataType: 'script'
        //         }).then(function() {
        //             if ('function' === typeof Auth0) {
        //                 // auth0 = new Auth0({
        //                 //     domain: ServerConfig.values.external_libraries.auth0.domain,
        //                 //     clientID: ServerConfig.values.external_libraries.auth0.client_id,
        //                 //     callbackOnLocationHash: true
        //                 // });
        //             }
        //             count = count + 1;
        //             if (count >= libraries.length) {
        //                 EventDispatcher.trigger('ready');
        //             }
        //         }).fail(function() {
        //             Debugger.error('Required library could not be loaded!');
        //         })
        //     };
        // }

        // loadLibraries();
    }

    {{ loadModule('modal') }}
    {{ loadModule('component-loader') }}

    {{ loadFile('ajax') }}
    {{ loadFile('client-config') }}
    {{ loadFile('account-manager') }}
    {{ loadFile('debugger') }}
    {{ loadFile('utils') }}

})(window.Radix = window.Radix || {});
