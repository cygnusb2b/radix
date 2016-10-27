React.createClass({ displayName: 'ComponentRegisterVerify',

    getDefaultProps: function() {
        return {
            emailAddress : null,
            accountId    : null
        };
    },

    getInitialState: function() {
        return {};
    },

    render: function() {
        var support       = Application.settings.support       || {};
        var notifications = Application.settings.notifications || {};
        return (
            React.createElement('div', { className: 'card card-block' },
                React.createElement('h2', { className: 'card-title' }, 'Thank you for signing up!'),
                React.createElement('p', { className: 'alert alert-info', role: 'alert' }, 'Before you can log in, you must ', React.createElement('strong', null, 'verify'), ' your email address'),
                React.createElement('p', { className: 'card-text'}, 'A verification email from  ', React.createElement('i', null, notifications.name + ' <' + notifications.email + '>'), ' will be delivered to the inbox of ', React.createElement('strong', null, this.props.emailAddress), '. To complete the process, please open the email and click the verification link within. If you\'re having difficulty finding the email, please check your spam and/or clutter folders.'),
                React.createElement('h5', { className: 'card-subtitle m-t-1'}, 'Still no email?'),
                React.createElement(Radix.Components.get('ResendVerifyEmail'), {
                    emailAddress : this.props.emailAddress,
                    accountId    : this.props.accountId
                }),
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
