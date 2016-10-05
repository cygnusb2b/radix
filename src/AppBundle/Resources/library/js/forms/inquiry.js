React.createClass({ displayName: 'FormInquiry',

    // @todo:: Should these forms take the customer object directly, or simply the form field values??
    getDefaultProps: function() {
        return {
            customer    : {},
            model       : {},
            onSubmit    : function(event) { Debugger.error('Nothing handled the form submit.');     },
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
            React.createElement('div', null,
                React.createElement(Radix.Components.get('FormInputText'), { ref: this.props.fieldRef, name: 'customer:givenName', wrapperClass: 'givenName', label: 'First Name', required: true, value: customer.givenName }),
                React.createElement(Radix.Components.get('FormInputText'), { ref: this.props.fieldRef, name: 'customer:familyName', wrapperClass: 'familyName', label: 'Last Name', required: true, value: customer.familyName })
            ),
            React.createElement('div', null,
                React.createElement(Radix.Components.get('FormInputText'), { ref: this.props.fieldRef, type: 'email', name: 'customer:primaryEmail', wrapperClass: 'email', label: 'Email Address', required: !disableEmail, readonly: disableEmail, value: customer.primaryEmail }),
                React.createElement(Radix.Components.get('FormInputText'), { ref: this.props.fieldRef, type: 'tel', name: 'customer:primaryPhone.number', wrapperClass: 'phone', label: phoneLabel, value: customer.primaryPhone.number })
            ),
            React.createElement('div', null,
                React.createElement(Radix.Components.get('FormInputText'), { ref: this.props.fieldRef, name: 'customer:companyName', wrapperClass: 'companyName', label: 'Company Name', value: customer.companyName }),
                React.createElement(Radix.Components.get('FormInputText'), { ref: this.props.fieldRef, name: 'customer:title', wrapperClass: 'title', label: 'Job Title', value: customer.title })
            ),
            React.createElement('div', null,
                React.createElement(Radix.Components.get('CountryPostalCode'), { fieldRef: this.props.fieldRef, postalCode: customer.primaryAddress.postalCode, countryCode: customer.primaryAddress.countryCode })
            ),
            React.createElement('div', null,
                React.createElement(Radix.Components.get('FormQuestion'), { fieldRef: this.props.fieldRef, keyOrId: 'purchase-intent', answers: customer.answers }),
                React.createElement(Radix.Components.get('FormQuestion'), { fieldRef: this.props.fieldRef, tagKeyOrId: 'business-code', answers: customer.answers }),
                React.createElement(Radix.Components.get('FormQuestion'), { fieldRef: this.props.fieldRef, tagKeyOrId: 'title-code', answers: customer.answers }),
                React.createElement(Radix.Components.get('FormQuestion'), { fieldRef: this.props.fieldRef, keyOrId: 'comments', answers: customer.answers })
            ),
            React.createElement('button', { type: 'submit'}, 'Submit')
        );
    }
});
