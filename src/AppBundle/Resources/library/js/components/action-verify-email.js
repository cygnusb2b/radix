React.createClass({ displayName: 'ComponentActionVerifyEmail',

    componentDidMount: function() {
        this.verify();
    },

    getDefaultProps: function() {
        return {
            token: null
        };
    },

    getInitialState: function() {
        return {
            successful: false,
            sending: true,
            canResend: false,
            meta: {}
        };
    },

    verify: function(event) {
        if (event) event.preventDefault();

        var locker = this._formLock;
        var error  = this._error;

        locker.lock();
        error.clear();

        var data = {
            'submission:token': this.props.token
        };

        data['submission:referringHost'] = window.location.protocol + '//' + window.location.host;
        data['submission:referringHref'] = window.location.href;

        var sourceKey = 'customer-email.verify-submit';
        var payload   = {
            data: data
        };

        Debugger.info('ComponentActionVerifyEmail', 'verify', sourceKey, payload);

        Ajax.send('/app/submission/' + sourceKey, 'POST', payload).then(function(response) {
            locker.unlock();

            CustomerManager.reloadCustomer().then(function() {
                EventDispatcher.trigger('CustomerManager.customer.loaded');
            });

            this.setState({ successful: true, sending: false });

        }.bind(this), function(jqXHR) {
            locker.unlock();
            this.setState({ sending: false });
            var meta = this._error.getMeta(jqXHR);
            if (403 === this._error.getStatusCodeFrom(jqXHR)) {
                this.setState({ canResend: true, meta: meta });
            }
            this._error.displayAjaxError(jqXHR);
        }.bind(this));
    },

    render: function() {
        var resend;
        if (this.state.canResend) {
            resend = React.createElement(Radix.Components.get('ResendVerifyEmail'), {
                emailAddress : this.state.meta.email,
                customerId   : this.state.meta.customer
            });
        } else if (this.state.sending) {
            resend = React.createElement('p', null, 'Verifying...');
        } else if (this.state.successful) {
            resend = React.createElement('p', { className: 'alert-success alert', role: 'alert' },
                React.createElement('strong', null, 'Success!'), ' Your email address is now verified and you\'re logged in.'
            );
        }
        return (
            React.createElement('div', null,
                React.createElement('h2', null, 'Account Email Verification'),
                React.createElement(Radix.Components.get('FormErrors'), { ref: this._setErrorDisplay }),
                resend,
                React.createElement(Radix.Components.get('FormLock'),   { ref: this._setLock })
            )
        )
    },

    _setLock: function(ref) {
        this._formLock = ref;
    },
    _setErrorDisplay: function(ref) {
        this._error = ref;
    },
});
