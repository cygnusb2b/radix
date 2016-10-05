React.createClass({ displayName: 'ComponentActionVerifyEmail',

    getDefaultProps: function() {
        return {};
    },

    getInitialState: function() {
        return {};
    },

    render: function() {
        return (
            React.createElement('div', null,
                React.createElement('h2', null, 'Verifing your email address!')
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
