function ClientConfig(config)
{
    config = 'object' === typeof config ? config : {};

    var defaults = {
        debug: false,
        host: null,
        appId: null,

        modules: {
            inquiry: {
                target: '.radix-module-inquiry'
            }
        },

        // -------///

        bindTarget: null,
        loginTitle: 'Log In',
        registerTitle: 'Sign Up',
        comments: {
            bindTarget: 'platformComments',
            detachedCount: {
                bindTarget: 'platformCommentsCount'
            }
        },
        targets: {
            loginButton: '.platform-login',
            registerButton: '.platform-register',
            logoutButton: '.platform-logout',
            reviewContainer: 'platformReviews',
            inquiryContainer: 'platformInquiry',
            guidrSubmit: '.guidr-submit'
        },
        reviewIdentifier: null,
        callbacks: {
            checkAuth: undefined
        },
        streamTitle: null,
        streamUrl: null
    };

    $.extend(defaults, config);
    this.values = defaults;

    if (config.debug) {
        Radix.setDebug(this.values.debug);
    }
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
