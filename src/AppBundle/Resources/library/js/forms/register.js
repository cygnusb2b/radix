React.createClass({ displayName: 'FormRegister',

    getDefaultProps: function() {
        return {
            account     : {
                primaryAddress: {},
                primaryPhone: {}
            },
            onSubmit    : function(event) { Debugger.error('Nothing handled the form submit.')  },
            fieldRef    : function(input) { Debugger.error('Nothing handled the field reference.'); }
        }
    },

    render: function() {
        return (this._getForm())
    },

    _getForm: function() {
        var account    = this.props.account;

        return React.createElement('form', { autocomplete: false, className: 'database-form', onSubmit: this.props.onSubmit },
            React.createElement('div', null,
                React.createElement(Radix.Components.get('FormInputText'), { ref: this.props.fieldRef, name: 'identity:givenName', wrapperClass: 'givenName', label: 'First Name', required: true, value: account.givenName }),
                React.createElement(Radix.Components.get('FormInputText'), { ref: this.props.fieldRef, name: 'identity:familyName', wrapperClass: 'familyName', label: 'Last Name', required: true, value: account.familyName })
            ),
            React.createElement('div', null,
                React.createElement(Radix.Components.get('FormInputText'), { ref: this.props.fieldRef, type: 'email', name: 'identity:primaryEmail', wrapperClass: 'email', label: 'Email Address', required: true, value: account.primaryEmail }),
                React.createElement(Radix.Components.get('FormInputText'), { ref: this.props.fieldRef, type: 'password', name: 'identity:password', wrapperClass: 'password', label: 'Password', required: true, value: null })
            ),
            React.createElement('div', null,
                React.createElement(Radix.Components.get('FormInputText'), { ref: this.props.fieldRef, name: 'identity:companyName', wrapperClass: 'companyName', label: 'Company Name', value: account.companyName, required: true }),
                React.createElement(Radix.Components.get('FormInputText'), { ref: this.props.fieldRef, name: 'identity:title', wrapperClass: 'title', label: 'Job Title', value: account.title, required: true })
            ),
            React.createElement('div', null,
                React.createElement(Radix.Components.get('CountryPostalCode'), { fieldRef: this.props.fieldRef, postalCode: account.primaryAddress.postalCode, countryCode: account.primaryAddress.countryCode, required: true })
            ),
            React.createElement('div', null,
                React.createElement(Radix.Components.get('FormQuestion'), { fieldRef: this.props.fieldRef, tagKeyOrId: 'business-code', answers: account.answers, required: true }),
                React.createElement(Radix.Components.get('FormQuestion'), { fieldRef: this.props.fieldRef, tagKeyOrId: 'title-code', answers: account.answers, required: true })
            ),
            React.createElement('button', { type: 'submit'}, 'Submit')
        );
    }
});
