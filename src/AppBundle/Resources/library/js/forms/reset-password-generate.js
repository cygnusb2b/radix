React.createClass({ displayName: 'FormResetPasswordGenerate',

    getDefaultProps: function() {
        return {
            onSubmit    : function(event) { Debugger.error('Nothing handled the form submit.');     },
            fieldRef    : function(input) { Debugger.error('Nothing handled the field reference.'); }
        }
    },

    render: function() {
        return (this._getForm())
    },

    _getForm: function() {

        return React.createElement('form', { className: 'database-form', onSubmit: this.props.onSubmit },
            React.createElement(Radix.Components.get('FormInputText'), { ref: this.props.fieldRef, name: 'identity:primaryEmail', label: 'Username or Email', required: true, wrapperClass: 'username' }),
            React.createElement('button', { type: 'submit'}, 'Submit')
        );
    }
});
