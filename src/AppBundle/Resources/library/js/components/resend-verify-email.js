React.createClass({ displayName: 'ComponentResendVerifyEmail',

    getDefaultProps: function() {
        return {
            className    : 'alert alert-info',
            emailAddress : null,
            accountId    : null,
            display      : true
        };
    },

    getInitialState: function() {
        return {
            display   : this.props.display,
            succeeded : false
        };
    },

    send: function(event) {
        event.preventDefault();

        var error  = this._error;
        error.clear();

        var locker = this._formLock;
        locker.lock();

        var data = {
            'identity:primaryEmail'    : this.props.emailAddress,
            'identity:id'              : this.props.accountId,
            'submission:referringHost' : window.location.protocol + '//' + window.location.host,
            'submission:referringHref' : window.location.href
        };

        var sourceKey = 'identity-account-email.verify-generate';
        var payload   = {
            data: data
        };

        Debugger.info('ComponentResendVerifyEmail', 'handleSubmit', sourceKey, payload);

        Ajax.send('/app/submission/' + sourceKey, 'POST', payload).then(function(response) {
            locker.unlock();
            this.setState({ succeeded: true });

        }.bind(this), function(jqXHR) {
            locker.unlock();
            error.displayAjaxError(jqXHR);
        }.bind(this));
    },

    render: function() {
        var elements;
        if (this.state.display) {
            var support  = Application.settings.support || {};
            elements = React.createElement('p', { className: this.props.className },
                React.createElement('span', null, 'To receive a new verification code, you can ', React.createElement('a', { href: 'javascript:void(0)', onClick: this.send }, 'resend'), ' the verification message to ', this.props.emailAddress, '. '),
                React.createElement('span', { className: 'card-text'}, 'If you\'re still experiencing issues, you can ', React.createElement('a', { href: 'mailto:' + support.email }, 'contact'), ' our support team for further assistance.')
            );

            if (this.state.succeeded) {
                elements = React.createElement('p', { className: 'alert-success alert', role: 'alert' },
                    React.createElement('strong', null, 'Success!'), ' The verification email was sent to ', React.createElement('strong', null, this.props.emailAddress), '.'
                );
            }
        }
        return (
            React.createElement('div', null,
                elements,
                React.createElement(Radix.Components.get('FormErrors'), { ref: this._setErrorDisplay }),
                React.createElement(Radix.Components.get('FormLock'), { ref: this._setLock })
            )
        )
    },

    clearError: function() {
        this._error.clear();
        return this;
    },

    hide: function() {
        this.setState({ display: false });
        return this;
    },

    show: function() {
        this.setState({ display: true });
        return this;
    },

    _setLock: function(ref) {
        this._formLock = ref;
    },
    _setErrorDisplay: function(ref) {
        this._error = ref;
    },
});
