React.createClass({ displayName: 'FormGatedDownload',
    // @todo Ultimately, specifying a gate should be a seperate concern from the form.
    // For example, Radix would be told to gate "something," and that directive would then determine what form to use.
    // Gating could be by registration: meaning, if logged in, can view, if not must register
    // Gating could be by a form: meaning, a form must be submitted before completion, regardless if logged in (though logged in would pre-pop)
    // Gating could by by a product subscription: meaning, a user must be subscribed to "something" in order to view
    // The functional response of the form (whether it's to "view" or "download") is simply a different directive, not a different component (which it is now)
    // So, this needs to be re-explored.
    getDefaultProps: function() {
        return {
            account  : {
                primaryAddress: {},
                primaryPhone: {}
            },
            onSubmit : function(event) { Debugger.error('Nothing handled the form submit.')  },
            fieldRef : function(input) { Debugger.error('Nothing handled the field reference.'); }
        }
    },

    render: function() {
        return (this._getForm())
    },

    _getForm: function() {
        var account      = this.props.account;
        var phoneType    = account.primaryPhone.phoneType || 'Phone';
        var phoneLabel   = phoneType + ' #';
        var disableEmail = (account._id && account.primaryEmail) ? true : false;

        return React.createElement('form', { autocomplete: false, className: 'database-form', onSubmit: this.props.onSubmit },
            React.createElement(Radix.Components.get('FormInputHidden'), { ref: this.props.fieldRef, name: 'identity:primaryAddress.identifier', value: account.primaryAddress.identifier }),
            React.createElement(Radix.Components.get('FormInputText'), { ref: this.props.fieldRef, name: 'identity:givenName', wrapperClass: 'givenName', label: 'First Name', required: true, value: account.givenName }),
            React.createElement(Radix.Components.get('FormInputText'), { ref: this.props.fieldRef, name: 'identity:familyName', wrapperClass: 'familyName', label: 'Last Name', required: true, value: account.familyName }),
            React.createElement(Radix.Components.get('FormInputText'), { ref: this.props.fieldRef, type: 'email', name: 'identity:primaryEmail', wrapperClass: 'email', label: 'Email Address', required: !disableEmail, readonly: disableEmail, value: account.primaryEmail }),
            React.createElement(Radix.Components.get('FormInputText'), { ref: this.props.fieldRef, name: 'identity:companyName', wrapperClass: 'companyName', label: 'Company Name', required: true, value: account.companyName }),
            React.createElement(Radix.Components.get('FormInputText'), { ref: this.props.fieldRef, name: 'identity:title', wrapperClass: 'title', label: 'Job Title', required: true, value: account.title }),
            React.createElement(Radix.Components.get('CountryPostalCode'), { fieldRef: this.props.fieldRef, postalCode: account.primaryAddress.postalCode, countryCode: account.primaryAddress.countryCode, required: true }),
            React.createElement(Radix.Components.get('FormQuestion'), { fieldRef: this.props.fieldRef, tagKeyOrId: 'business-code', required: true, answers: account.answers }),
            React.createElement(Radix.Components.get('FormQuestion'), { fieldRef: this.props.fieldRef, tagKeyOrId: 'title-code', required: true, answers: account.answers }),
            React.createElement('button', { type: 'submit'}, 'Submit')
        );
    }
});
