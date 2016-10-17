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
            primaryEmail : null
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

        var sourceKey = 'customer-account.reset-password';
        var payload   = {
            data: data
        };

        Debugger.info('ComponentResetPassword', 'handleSubmit()', sourceKey, payload);
    },

    verifyToken: function() {

        var token  = this.props.token;
        var locker = this._formLock;
        var error  = this._error;

        locker.lock();
        error.clear();

        Debugger.info('ComponentResetPassword', 'verifyToken', token);

        if (CustomerManager.isLoggedIn()) {
            CustomerManager.logout().then(function() {
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
            // Show form
            elements = React.createElement('div', null,
                React.createElement('p', { className: 'alert-info alert', role: 'alert' }, 'Reseting password for ', React.createElement('strong', null, this.state.primaryEmail), '.'),
                React.createElement(Radix.Forms.get('ResetPassword'), {
                    onSubmit : this.handleSubmit,
                    fieldRef : this.handleFieldRef
                })
            );
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
