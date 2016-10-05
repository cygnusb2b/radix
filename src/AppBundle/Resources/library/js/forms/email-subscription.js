React.createClass({ displayName: 'FormEmailSubscription',

    // @todo:: Should these forms take the customer object directly, or simply the form field values??
    getDefaultProps: function() {
        return {
            customer    : {},
            model       : {},
            onSubmit    : function(event) { Debugger.error('Nothing handled the form submit.');     },
            onChange    : function(event) { Debugger.error('Nothing handled the field change.');    },
            fieldRef    : function(input) { Debugger.error('Nothing handled the field reference.'); }
        }
    },

    render: function() {
        return (this._getForm())
    },

    _getForm: function() {
        var handleChange = this.props.onChange;
        var customer     = this.props.customer;
        var disableEmail = (customer._id) ? true : false;
        var phoneType    = customer.primaryPhone.phoneType || 'Phone';
        var phoneLabel   = phoneType + ' #';

        return React.createElement('form', { autocomplete: false, className: 'database-form', onSubmit: this.props.onSubmit },
            React.createElement(Radix.Components.get('FormInputText'), { ref: this.props.fieldRef, onChange: handleChange, type: 'email', name: 'customer:primaryEmail', wrapperClass: 'email', label: 'Email Address', required: !disableEmail, readonly: disableEmail, value: customer.primaryEmail }),
            React.createElement(Radix.Components.get('FormInputText'), { ref: this.props.fieldRef, onChange: handleChange, name: 'customer:givenName', wrapperClass: 'givenName', label: 'First Name', required: true, value: customer.givenName }),
            React.createElement(Radix.Components.get('FormInputText'), { ref: this.props.fieldRef, onChange: handleChange, name: 'customer:familyName', wrapperClass: 'familyName', label: 'Last Name', required: true, value: customer.familyName }),
            React.createElement(Radix.Components.get('FormInputText'), { ref: this.props.fieldRef, onChange: handleChange, name: 'customer:companyName', wrapperClass: 'companyName', label: 'Company Name', value: customer.companyName }),
            React.createElement(Radix.Components.get('FormInputText'), { ref: this.props.fieldRef, onChange: handleChange, name: 'customer:title', wrapperClass: 'title', label: 'Job Title', value: customer.title }),
            React.createElement(Radix.Components.get('FormSelectCountry'), { fieldRef: this.props.fieldRef, onChange: handleChange, name: 'customer:primaryAddress.countryCode', wrapperClass: 'countryCode', selected: customer.primaryAddress.countryCode }),
            React.createElement(Radix.Components.get('FormInputText'), { ref: this.props.fieldRef, onChange: handleChange, type: 'tel', name: 'customer:primaryPhone.number', wrapperClass: 'phone', label: phoneLabel, value: customer.primaryPhone.number }),
            React.createElement(Radix.Components.get('FormQuestion'), { fieldRef: this.props.fieldRef, onChange: handleChange, tagKeyOrId: 'business-code', answers: customer.answers }),
            React.createElement(Radix.Components.get('FormQuestion'), { fieldRef: this.props.fieldRef, onChange: handleChange, tagKeyOrId: 'title-code', answers: customer.answers }),
            React.createElement(Radix.Components.get('FormQuestion'), { fieldRef: this.props.fieldRef, onChange: handleChange, tagKeyOrId: 'employee-size', answers: customer.answers }),
            React.createElement(Radix.Components.get('FormQuestion'), { fieldRef: this.props.fieldRef, onChange: handleChange, tagKeyOrId: 'sales-volume', answers: customer.answers }),
            React.createElement('button', { type: 'submit'}, 'Submit')
        );
    }
});
