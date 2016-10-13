React.createClass({ displayName: 'ComponentLogin',
    getDefaultProps: function() {
        return {
            title     : 'Log In',
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

        var payload   = {
            data: data
        };

        Debugger.info('ComponentLogin', 'handleSubmit()', payload);

        CustomerManager.databaseLogin(payload).then(function(response) {
            locker.unlock();

            if (Utils.isFunction(this.props.onSuccess)) {
                this.props.onSuccess(response);
            }

        }.bind(this), function(response) {
            locker.unlock();
            error.displayAjaxError(response);

            if (Utils.isFunction(this.props.onFailure)) {
                this.props.onFailure(response);
            }

        }.bind(this));
    },

    getInitialState: function() {
        return {
            loggedIn: CustomerManager.isLoggedIn()
        }
    },

    componentDidMount: function() {
        EventDispatcher.subscribe('CustomerManager.customer.loaded', function() {
            this.setState({ loggedIn: true });
        }.bind(this));

        EventDispatcher.subscribe('CustomerManager.customer.unloaded', function() {
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
            React.createElement('h5', null, 'You are currently logged in.')
        );
    },

    _getLoggedOutElements: function() {
        return React.createElement('div', null,
            React.createElement(Radix.Forms.get('Login'), {
                onSubmit : this.handleSubmit,
                fieldRef : this.handleFieldRef
            }),
            React.createElement('p', { className: 'text-center muted' }, 'Need an account? ',
                // @todo This should use the register modal link component!!
                React.createElement('a', { href: 'javascript:void(0)', onClick: Radix.SignIn.register }, 'Sign up!')
                // React.createElement('br'),
                // React.createElement('a', {href: 'javascript:void(0)', onClick: Radix.SignIn.reset}, 'Forgot your password?')
            ),
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
