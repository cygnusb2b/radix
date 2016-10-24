React.createClass({ displayName: 'ComponentRegister',
    getDefaultProps: function() {
        return {
            title     : 'Register',
            onSuccess : null,
            onFailure : null
        };
    },

    getInitialState: function() {
        return {
            loggedIn : AccountManager.isLoggedIn(),
            verify   : null
        };
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

        var payload   = {
            data: data
        };

        Debugger.info('ComponentRegister', 'handleSubmit()', payload);

        if (false === this._validateSubmit(data)) {
            locker.unlock();
            return;
        }

        AccountManager.register(payload).then(function(response) {
            locker.unlock();
            var verify = {
                emailAddress : response.data.email,
                accountId    : response.data.account
            };
            this.setState({ verify: verify });
        }.bind(this), function(response) {
            locker.unlock();
            error.displayAjaxError(response);
        });
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
            React.createElement('h5', null, 'You are currently logged in.')
        );
    },

    _getLoggedOutElements: function() {
        var elements;
        if (!this.state.verify) {
            elements = React.createElement('div', null,
                React.createElement(Radix.Forms.get('Register'), {
                    onSubmit : this.handleSubmit,
                    fieldRef : this.handleFieldRef
                }),
                React.createElement('p', { className: 'muted text-center' }, 'Already have an account? ',
                    React.createElement(Radix.Components.get('ModalLinkLogin'), { label: 'Sign in!' })
                ),
                React.createElement(Radix.Components.get('FormErrors'), { ref: this._setErrorDisplay }),
                React.createElement(Radix.Components.get('FormLock'),   { ref: this._setLock })
            );
        } else {
            elements = React.createElement('div', null,
                React.createElement(Radix.Components.get('RegisterVerify'), this.state.verify)
            );
        }
        return elements;
    },

    _formRefs: {},

    _setErrorDisplay: function(ref) {
        this._error = ref;
    },

    _setLock: function(ref) {
        this._formLock = ref;
    },

    _validateSubmit: function(data) {
        var error = this._error;
        if (!data['identity:password']) {
            error.display('The password field is required.');
            return false;
        }
        if (data['identity:password'].length < 4) {
            error.display('The password must be at least 4 characters long.');
            return false;
        }
        if (data['identity:password'].length > 72) {
            error.display('The password cannot be longer than 72 characters.');
            return false;
        }
        return true;
    }
});
