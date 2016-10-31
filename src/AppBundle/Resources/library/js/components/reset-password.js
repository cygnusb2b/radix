React.createClass({ displayName: 'ComponentResetPassword',

    componentDidMount: function() {
        this.verifyToken();
    },

    getDefaultProps: function() {
        return {
            title: 'Reset Password',
            token: null
        };
    },

    getInitialState: function() {
        return {
            verifying    : true,
            verified     : false,
            primaryEmail : null,
            succeeded    : false,
        };
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

        data['submission:token']         = this.props.token;
        data['submission:referringHost'] = window.location.protocol + '//' + window.location.host;
        data['submission:referringHref'] = window.location.href;

        var sourceKey = 'identity-account.reset-password';
        var payload   = {
            data: data
        };

        Debugger.info('ComponentResetPassword', 'handleSubmit()', sourceKey, payload);

        Ajax.send('/app/submission/' + sourceKey, 'POST', payload).then(function(response) {
            locker.unlock();
            this.setState({ succeeded: true });

            AccountManager.reloadAccount().then(function() {
                EventDispatcher.trigger('AccountManager.account.loaded');
            });

        }.bind(this), function(jqXHR) {
            locker.unlock();
            error.displayAjaxError(jqXHR);
        }.bind(this));
    },

    verifyToken: function() {

        var token  = this.props.token;
        var locker = this._formLock;
        var error  = this._error;

        locker.lock();
        error.clear();

        Debugger.info('ComponentResetPassword', 'verifyToken', token);

        if (AccountManager.isLoggedIn()) {
            AccountManager.logout().then(function() {
                this._doTokenVerify(token);
            }.bind(this));
        } else {
            this._doTokenVerify(token);
        }
    },

    _doTokenVerify: function(token) {
        var locker = this._formLock;
        var error  = this._error;

        Ajax.send('/app/auth/verify-reset-token/' + token, 'GET').then(function(response) {
            locker.unlock();
            this.setState({ verified: true, verifying: false, primaryEmail: response.data.primaryEmail });
        }.bind(this), function(jqXHR) {
            locker.unlock();
            this.setState({ verifying: false });
            this._error.displayAjaxError(jqXHR);
        }.bind(this));
    },

    render: function() {
        var elements;
        if (this.state.verifying) {
            elements = React.createElement('p', { className: 'alert-info alert', role: 'alert' }, React.createElement('strong', null, 'One moment please.'), ' Verifying password reset link...');
        } else if (this.state.verified) {
            if (this.state.succeeded) {
                // Show success.
                elements = React.createElement('p', { className: 'alert-success alert', role: 'alert' }, 'Password successfully reset for ', React.createElement('strong', null, this.state.primaryEmail), '. You are now logged in.');
            } else {
                // Show form.
                elements = React.createElement('div', null,
                    React.createElement('p', { className: 'alert-info alert', role: 'alert' }, 'Reseting password for ', React.createElement('strong', null, this.state.primaryEmail), '.'),
                    React.createElement(Radix.Forms.get('ResetPassword'), {
                        onSubmit : this.handleSubmit,
                        fieldRef : this.handleFieldRef
                    })
                );
            }
        }

        return (
            React.createElement('div', null,
                React.createElement('h2', null, this.props.title),
                elements,
                React.createElement(Radix.Components.get('ContactSupport'), { opening: 'Having trouble resting your password?' }),
                React.createElement(Radix.Components.get('FormErrors'), { ref: this._setErrorDisplay }),
                React.createElement(Radix.Components.get('FormLock'),   { ref: this._setLock })
            )
        )
    },

    _formRefs: {},

    handleFieldRef: function(input) {
        if (input) {
            this._formRefs[input.props.name] = input;
        }
    },

    _setLock: function(ref) {
        this._formLock = ref;
    },
    _setErrorDisplay: function(ref) {
        this._error = ref;
    },
});
