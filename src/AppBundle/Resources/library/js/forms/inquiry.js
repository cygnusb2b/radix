React.createClass({ displayName: 'FormInquiry',

    // @todo:: Should these forms take the customer object directly, or simply the form field values??
    getDefaultProps: function() {
        return {
            customer    : {},
            model       : {},
            nextTemplate: null,
            onSubmit    : function(event) { Debugger.error('Nothing handled the form submit.')  },
            onChange    : function(event) { Debugger.error('Nothing handled the field change.') }
        }
    },

    render: function() {
        var element;
        if (this.props.nextTemplate) {
            element = React.createElement('div', { dangerouslySetInnerHTML: { __html: this.props.nextTemplate } });
        } else {
            element = this._getForm();
        }

        return (element)
    },

    _getForm: function() {
        var handleChange = this.props.onChange;
        var customer     = this.props.customer;
        var disableEmail = (customer._id) ? true : false;
        var phoneType    = customer.primaryPhone.phoneType || 'Phone';
        var phoneLabel   = phoneType + ' #';

        return React.createElement('form', { autocomplete: false, className: 'database-form', onSubmit: this.props.onSubmit },
            React.createElement('div', null,
                React.createElement(Radix.Components.get('FormInputText'), { onChange: handleChange, name: 'customer:givenName', wrapperClass: 'givenName', label: 'First Name', required: true, value: customer.givenName }),
                React.createElement(Radix.Components.get('FormInputText'), { onChange: handleChange, name: 'customer:familyName', wrapperClass: 'familyName', label: 'Last Name', required: true, value: customer.familyName })
            ),
            React.createElement('div', null,
                React.createElement(Radix.Components.get('FormInputText'), { onChange: handleChange, type: 'email', name: 'customer:primaryEmail', wrapperClass: 'email', label: 'Email Address', required: !disableEmail, readonly: disableEmail, value: customer.primaryEmail }),
                React.createElement(Radix.Components.get('FormInputText'), { onChange: handleChange, type: 'tel', name: 'customer:primaryPhone.number', wrapperClass: 'phone', label: phoneLabel, value: customer.primaryPhone.number })
            ),
            React.createElement('div', null,
                React.createElement(Radix.Components.get('FormInputText'), { onChange: handleChange, name: 'customer:companyName', wrapperClass: 'companyName', label: 'Company Name', value: customer.companyName }),
                React.createElement(Radix.Components.get('FormInputText'), { onChange: handleChange, name: 'customer:title', wrapperClass: 'title', label: 'Job Title', value: customer.title })
            ),
            React.createElement('div', null,
                React.createElement(Radix.Components.get('CountryPostalCode'), { onChange: handleChange, postalCode: customer.primaryAddress.postalCode, countryCode: customer.primaryAddress.countryCode })
            ),
            React.createElement('div', null,
                React.createElement(Radix.Components.get('FormQuestion'), { onChange: handleChange, keyOrId: 'purchase-intent', answers: customer.answers }),
                React.createElement(Radix.Components.get('FormQuestion'), { onChange: handleChange, tagKeyOrId: 'business-code', answers: customer.answers }),
                React.createElement(Radix.Components.get('FormQuestion'), { onChange: handleChange, tagKeyOrId: 'title-code', answers: customer.answers }),
                React.createElement(Radix.Components.get('FormQuestion'), { onChange: handleChange, keyOrId: 'comments', answers: customer.answers })
            ),
            React.createElement('button', { type: 'submit'}, 'Submit')
        );
    }
});
