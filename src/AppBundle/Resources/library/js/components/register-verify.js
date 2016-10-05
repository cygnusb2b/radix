React.createClass({ displayName: 'ComponentRegisterVerify',

    getDefaultProps: function() {
        return {
            emailAddress: null,
            customerId: null
        };
    },

    getInitialState: function() {
        return {};
    },

    resend: function(event) {
        event.preventDefault();

        var error  = this._error;
        error.clear();

        var locker = this._formLock;
        locker.lock();

        var data = {
            'customer:primaryEmail'    : this.props.emailAddress,
            'submission:referringHost' : window.location.protocol + '//' + window.location.host,
            'submission:referringHref' : window.location.href
        };

        var sourceKey = 'customer-email.verify-generate';
        var payload   = {
            data: data
        };

        Debugger.info('EmailSubscriptionModule', 'handleSubmit', sourceKey, payload);

        Ajax.send('/app/submission/' + sourceKey, 'POST', payload).then(function(response) {
            locker.unlock();
            // @todo Display success message.
            // @todo More thoroughly, create a notifications/flash bag

        }.bind(this), function(jqXHR) {
            locker.unlock();
            error.displayAjaxError(jqXHR);
        }.bind(this));
    },

    render: function() {
        var support       = Application.settings.support       || {};
        var notifications = Application.settings.notifications || {};
        return (
            React.createElement('div', { className: 'card card-block' },
                React.createElement('h2', { className: 'card-title' }, 'Thank you for signing up!'),
                React.createElement('p', { className: 'alert alert-info', role: 'alert' }, 'Before you can log in, you must ', React.createElement('strong', null, 'verify'), ' your email address'),
                React.createElement('p', { className: 'card-text'}, 'A verification email from  ', React.createElement('i', null, notifications.name + ' <' + notifications.email + '>'), ' will be delivered to the inbox of ', React.createElement('strong', null, this.props.emailAddress), '. To complete the process, please open the email and click the verification link within. If you\'re having difficulty finding the email, please check your spam and/or clutter folders.'),
                React.createElement('p', { className: 'card-text'}, 'Still no verification email? You may ', React.createElement('a', { href: 'javascript:void(0)', onClick: this.resend }, 'resend'), ' the verification or ', React.createElement('a', { href: 'mailto:' + support.email }, 'contact'), ' our support team for further assistance.'),
                React.createElement(Radix.Components.get('FormErrors'), { ref: this._setErrorDisplay }),
                React.createElement(Radix.Components.get('FormLock'), { ref: this._setLock })
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
