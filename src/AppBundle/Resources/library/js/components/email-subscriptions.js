React.createClass({ displayName: 'ComponentEmailSubscriptions',

    componentDidMount: function() {
        EventDispatcher.subscribe('AccountManager.account.loaded', function() {
            var account = AccountManager.getAccount();
            this._loadOptinsFor(account.primaryEmail);
            this.setState({ account : account });
        }.bind(this));

        EventDispatcher.subscribe('AccountManager.account.unloaded', function() {
            var account = AccountManager.getAccount();
            this._loadOptinsFor(account.primaryEmail);
            this.setState({ account : account, nextTemplate: null });
        }.bind(this));

        this._loadOptinsFor(this.state.account.primaryEmail);
    },

    getDefaultProps: function() {
        return {
            title     : 'Manage Email Subscriptions',
            className : null,
        };
    },

    getInitialState: function() {
        return {
            account      : AccountManager.getAccount(),
            optIns       : {},
            nextTemplate : null
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
        Debugger.log('ComponentEmailSubscriptions', 'render()', this);

        var className = 'platform-element';
        if (this.props.className) {
            className = className + ' ' + this.props.className;
        }
        var elements;

        if (this.state.nextTemplate) {
            elements = React.createElement('div', { className: className, dangerouslySetInnerHTML: { __html: this.state.nextTemplate } });
        } else {
            elements = React.createElement('div', { className: className },
                React.createElement('h2', null, this.props.title),
                React.createElement(Radix.Components.get('ModalLinkLoginVerbose')),
                React.createElement('hr'),
                React.createElement('div', { className: 'email-subscription-wrapper' },
                    React.createElement(Radix.Components.get('FormProductsEmail'), {
                        fieldRef : this.handleFieldRef,
                        optIns   : this.state.optIns
                    }),
                    React.createElement(Radix.Forms.get('EmailSubscription'), {
                        account  : this.state.account,
                        onSubmit : this.handleSubmit,
                        fieldRef : this.handleFieldRef
                    })
                ),
                React.createElement(Radix.Components.get('FormErrors'), { ref: this._setErrorDisplay }),
                React.createElement(Radix.Components.get('FormLock'),   { ref: this._setLock })
            );
        }
        return (elements);
    },

    _loadOptinsFor: function(emailAddress) {
        var optIns = {}
        if (emailAddress) {
            Ajax.send('/app/opt-ins/email-deployment/' + emailAddress, 'GET').then(function(response) {
                this.setState({ optIns: response.data });
            }.bind(this), function() {
                this.setState({ optIns: optIns });
                Debugger.error('ComponentEmailSubscriptions _loadOptinsFor()', 'Unable to load optins.');
            }.bind(this));
        } else {
            this.setState({ optIns: optIns });
        }
    },

    _setErrorDisplay: function(ref) {
        this._error = ref;
    },

    _setLock: function(ref) {
        this._formLock = ref;
    },

});
