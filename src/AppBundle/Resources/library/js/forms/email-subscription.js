React.createClass({ displayName: 'FormEmailSubscription',

    // @todo:: Should these forms take the customer object directly, or simply the form field values??
    getDefaultProps: function() {
        return {
            account  : {},
            onSubmit : function(event) { Debugger.error('Nothing handled the form submit.');     },
            fieldRef : function(input) { Debugger.error('Nothing handled the field reference.'); }
        }
    },

    render: function() {
        return (this._getForm())
    },

    _getForm: function() {
        var account      = this.props.account;
        var disableEmail = (account._id) ? true : false;
        var phoneType    = account.primaryPhone.phoneType || 'Phone';
        var phoneLabel   = phoneType + ' #';

        return React.createElement('form', { autocomplete: false, className: 'database-form', onSubmit: this.props.onSubmit },
            React.createElement(Radix.Components.get('FormInputHidden'), { ref: this.props.fieldRef, name: 'identity:primaryAddress.identifier', value: account.primaryAddress.identifier }),
            React.createElement(Radix.Components.get('FormInputHidden'), { ref: this.props.fieldRef, name: 'identity:primaryPhone.identifier', value: account.primaryPhone.identifier }),
            React.createElement(Radix.Components.get('FormInputHidden'), { ref: this.props.fieldRef, name: 'identity:primaryEmail', value: account.primaryEmail }),
            React.createElement(Radix.Components.get('FormInputText'), { ref: this.props.fieldRef, type: 'email', name: 'submission:emailAddress', wrapperClass: 'email', label: 'Email Address', required: !disableEmail, readonly: disableEmail, value: account.primaryEmail }),
            React.createElement(Radix.Components.get('FormInputText'), { ref: this.props.fieldRef, name: 'identity:givenName', wrapperClass: 'givenName', label: 'First Name', required: true, value: account.givenName }),
            React.createElement(Radix.Components.get('FormInputText'), { ref: this.props.fieldRef, name: 'identity:familyName', wrapperClass: 'familyName', label: 'Last Name', required: true, value: account.familyName }),
            React.createElement(Radix.Components.get('FormInputText'), { ref: this.props.fieldRef, name: 'identity:companyName', wrapperClass: 'companyName', label: 'Company Name', required: true, value: account.companyName }),
            React.createElement(Radix.Components.get('FormInputText'), { ref: this.props.fieldRef, name: 'identity:title', wrapperClass: 'title', label: 'Job Title', required: true, value: account.title }),
            React.createElement(Radix.Components.get('CountryPostalCode'), { fieldRef: this.props.fieldRef, postalCode: account.primaryAddress.postalCode, countryCode: account.primaryAddress.countryCode, required: true }),
            React.createElement(Radix.Components.get('FormInputText'), { ref: this.props.fieldRef, type: 'tel', name: 'identity:primaryPhone.number', wrapperClass: 'phone', label: phoneLabel, value: account.primaryPhone.number }),
            React.createElement(Radix.Components.get('FormQuestion'), { fieldRef: this.props.fieldRef, tagKeyOrId: 'business-code', required: true, answers: account.answers }),
            React.createElement(Radix.Components.get('FormQuestion'), { fieldRef: this.props.fieldRef, tagKeyOrId: 'title-code', required: true, answers: account.answers }),
            React.createElement(Radix.Components.get('FormQuestion'), { fieldRef: this.props.fieldRef, tagKeyOrId: 'employee-size', answers: account.answers }),
            React.createElement(Radix.Components.get('FormQuestion'), { fieldRef: this.props.fieldRef, tagKeyOrId: 'sales-volume', answers: account.answers }),
            React.createElement('button', { type: 'submit'}, 'Submit')
        );
    }
});
