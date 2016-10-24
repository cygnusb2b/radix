React.createClass({ displayName: 'ComponentResendVerifyEmail',

    getDefaultProps: function() {
        return {
            className    : null,
            emailAddress : null,
            accountId    : null
        };
    },

    getInitialState: function() {
        return {
            succeeded: false
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

        var sourceKey = 'account-email.verify-generate';
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

        var support  = Application.settings.support || {};
        var elements = React.createElement('div', { className: this.props.className },
            React.createElement('p', null, 'To receive a new verification code, you can ', React.createElement('a', { href: 'javascript:void(0)', onClick: this.send }, 'resend'), ' the verification message to ', this.props.emailAddress, '.'),
            React.createElement('p', { className: 'card-text'}, 'If you\'re still experiencing issues, you can ', React.createElement('a', { href: 'mailto:' + support.email }, 'contact'), ' our support team for further assistance.'),
            React.createElement(Radix.Components.get('FormErrors'), { ref: this._setErrorDisplay }),
            React.createElement(Radix.Components.get('FormLock'), { ref: this._setLock })
        );

        if (this.state.succeeded) {
            elements = React.createElement('p', { className: 'alert-success alert', role: 'alert' },
                React.createElement('strong', null, 'Success!'), ' The verification email was sent to ', React.createElement('strong', null, this.props.emailAddress), '.'
            );
        }
        return (elements)
    },

    _setLock: function(ref) {
        this._formLock = ref;
    },
    _setErrorDisplay: function(ref) {
        this._error = ref;
    },
});
