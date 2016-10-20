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
            customer    : {
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
        var customer     = this.props.customer;
        var phoneType    = customer.primaryPhone.phoneType || 'Phone';
        var phoneLabel   = phoneType + ' #';
        var disableEmail = (customer._id && customer.primaryEmail) ? true : false;

        return React.createElement('form', { autocomplete: false, className: 'database-form', onSubmit: this.props.onSubmit },
            React.createElement(Radix.Components.get('FormInputText'), { ref: this.props.fieldRef, name: 'customer:givenName', wrapperClass: 'givenName', label: 'First Name', required: true, value: customer.givenName }),
            React.createElement(Radix.Components.get('FormInputText'), { ref: this.props.fieldRef, name: 'customer:familyName', wrapperClass: 'familyName', label: 'Last Name', required: true, value: customer.familyName }),
            React.createElement(Radix.Components.get('FormInputText'), { ref: this.props.fieldRef, type: 'email', name: 'customer:primaryEmail', wrapperClass: 'email', label: 'Email Address', required: !disableEmail, readonly: disableEmail, value: customer.primaryEmail }),
            React.createElement(Radix.Components.get('FormInputText'), { ref: this.props.fieldRef, name: 'customer:companyName', wrapperClass: 'companyName', label: 'Company Name', value: customer.companyName }),
            React.createElement(Radix.Components.get('FormInputText'), { ref: this.props.fieldRef, name: 'customer:title', wrapperClass: 'title', label: 'Job Title', value: customer.title }),
            React.createElement(Radix.Components.get('CountryPostalCode'), { fieldRef: this.props.fieldRef, postalCode: customer.primaryAddress.postalCode, countryCode: customer.primaryAddress.countryCode }),
            React.createElement(Radix.Components.get('FormQuestion'), { fieldRef: this.props.fieldRef, tagKeyOrId: 'business-code', answers: customer.answers }),
            React.createElement(Radix.Components.get('FormQuestion'), { fieldRef: this.props.fieldRef, tagKeyOrId: 'title-code', answers: customer.answers }),
            React.createElement('button', { type: 'submit'}, 'Submit')
        );
    }
});
