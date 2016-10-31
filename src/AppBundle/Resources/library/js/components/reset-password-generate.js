React.createClass({ displayName: 'ComponentResetPasswordGenerate',
    getDefaultProps: function() {
        return {
            title     : 'Reset Password',
            onSuccess : null,
            onFailure : null
        }
    },

    handleFieldRef: function(input) {
        if (input) {
            this._formRefs[input.props.name] = input;
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

        var sourceKey = 'identity-account.reset-password-generate';
        var payload   = {
            data: data
        };

        Debugger.info('ComponentResetPasswordGenerate', 'handleSubmit', sourceKey, payload);

        Ajax.send('/app/submission/' + sourceKey, 'POST', payload).then(function(response) {
            locker.unlock();
            this.setState({ succeeded: true });

        }.bind(this), function(jqXHR) {
            locker.unlock();
            error.displayAjaxError(jqXHR);
        });
    },

    getInitialState: function() {
        return {
            loggedIn  : AccountManager.isLoggedIn(),
            succeeded : false
        }
    },

    componentDidMount: function() {
        EventDispatcher.subscribe('AccountManager.account.loaded', function() {
            this.setState({ loggedIn: true });
        }.bind(this));

        EventDispatcher.subscribe('AccountManager.account.unloaded', function() {
            this.setState({ loggedIn: false });
        }.bind(this));
    },

    render: function() {
        var elements = (this.state.loggedIn) ? this._getLoggedInElements() : this._getLoggedOutElements();
        return (
            React.createElement('div', { className: 'login-list' },
                React.createElement('h2', { className: 'name' }, this.props.title),
                elements
            )
        );
    },

    _getLoggedInElements: function() {
        return React.createElement('div', null,
            React.createElement('h5', null, 'You are currently logged in. You may visit your profile to change your password.')
        );
    },

    _getLoggedOutElements: function() {
        var elements;
        if (this.state.succeeded) {
            elements = React.createElement('p', { className: 'alert-success alert', role: 'alert' }, 'The password reset email was successfully sent. Please ensure to check your span folders.');
        } else {
            elements = React.createElement(Radix.Forms.get('ResetPasswordGenerate'), {
                onSubmit : this.handleSubmit,
                fieldRef : this.handleFieldRef
            });
        }

        return React.createElement('div', null,
            React.createElement('p', null, 'To reset your password, enter your email address or username. A reset email will be sent to the primary email address on your account. Once the email arrives in your inbox, click the link provided to complete the reset process.'),
            elements,
            React.createElement(Radix.Components.get('ContactSupport'), { opening: 'Having trouble logging in?' }),
            React.createElement(Radix.Components.get('FormErrors'), { ref: this._setErrorDisplay }),
            React.createElement(Radix.Components.get('FormLock'),   { ref: this._setLock })
        );
    },

    _formRefs: {},

    _setErrorDisplay: function(ref) {
        this._error = ref;
    },

    _setLock: function(ref) {
        this._formLock = ref;
    }
});
