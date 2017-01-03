React.createClass({ displayName: 'ComponentComments',

    componentDidMount: function() {
        EventDispatcher.subscribe('AccountManager.account.loaded', function() {
            var account = AccountManager.getAccount();
            this.setState({ account : account, loggedIn : true });
        }.bind(this));

        EventDispatcher.subscribe('AccountManager.account.unloaded', function() {
            var account = AccountManager.getAccount();
            this.setState({ account : account, loggedIn : false });
        }.bind(this));
    },

    getDefaultProps: function() {
        return {
            title       : 'Join the conversation!',
            streamId    : null, // The unique stream identifier.
            streamTitle : null,
            streamUrl   : window.location.href,
            className   : null,
        };
    },

    getInitialState: function() {
        return {
            loggedIn : AccountManager.isLoggedIn(),
            account  : AccountManager.getAccount(),
            settings : Application.settings.posts,
        }
    },

    handleSubmit: function(event) {
        event.preventDefault();

        var locker = this._formLock;
        var error  = this._error;

        error.clear();
        locker.lock();

        var data = {};
        for (var name in this._formRefs) {
            var ref = this._formRefs[name];
            data[name] = ref.state.value;
        }

        data['submission:referringHost'] = window.location.protocol + '//' + window.location.host;
        data['submission:referringHref'] = window.location.href;

        var sourceKey = 'product-email-deployment-optin';
        var payload   = {
            data: data
        };

        Debugger.info('EmailSubscriptionModule', 'handleSubmit', sourceKey, payload);

        Ajax.send('/app/submission/' + sourceKey, 'POST', payload).then(function(response) {
            locker.unlock();

            // Refresh the account, if logged in.
            if (AccountManager.isLoggedIn()) {
                AccountManager.reloadAccount().then(function() {
                    EventDispatcher.trigger('AccountManager.account.loaded');
                });
            }

            // Set the next template to display (thank you page, etc).
            var template = (response.data) ? response.data.template || null : null;
            this.setState({ nextTemplate: template });

        }.bind(this), function(jqXHR) {
            locker.unlock();
            this._error.displayAjaxError(jqXHR);
        }.bind(this));
    },

    _formRefs: {},

    handleFieldRef: function(input) {
        if (input) {
            this._formRefs[input.props.name] = input;
        }
    },

    render: function() {
        Debugger.log('ComponentComments', 'render()', this);

        var className = 'platform-element';
        if (this.props.className) {
            className = className + ' ' + this.props.className;
        }
        var elements;

        if (this.state.settings.enabled) {
            elements = React.createElement('div', { className: className },
                React.createElement('h2', null, this.props.title),
                // @todo Need to determine if anonymous commenting is allowed. If so, the login/register isn't required.
                React.createElement('p', null,
                    React.createElement(Radix.Components.get('ModalLinkLogin'), {
                        wrappingTag : 'span',
                        prefix      : 'This site requires you to',
                        label       : 'login',
                        suffix      : 'or ',
                    }),
                    React.createElement(Radix.Components.get('ModalLinkRegister'), {
                        wrappingTag : 'span',
                        label       : 'register',
                        suffix      : 'to post a comment.',
                    })
                ),

                // React.createElement(Radix.Components.get('ModalLinkLoginVerbose')),
                React.createElement('hr'),
                // React.createElement('div', { className: 'email-subscription-wrapper' },
                //     React.createElement(Radix.Components.get('FormProductsEmail'), {
                //         fieldRef : this.handleFieldRef,
                //         optIns   : this.state.optIns
                //     }),
                //     React.createElement(Radix.Forms.get('EmailSubscription'), {
                //         account  : this.state.account,
                //         onSubmit : this.handleSubmit,
                //         fieldRef : this.handleFieldRef
                //     })
                // ),
                React.createElement(Radix.Components.get('FormErrors'), { ref: this._setErrorDisplay }),
                React.createElement(Radix.Components.get('FormLock'),   { ref: this._setLock })
            );
        }
        return (elements);
    },

    _setErrorDisplay: function(ref) {
        this._error = ref;
    },

    _setLock: function(ref) {
        this._formLock = ref;
    },

});
