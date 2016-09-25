React.createClass({ displayName: 'FormInquiry',

    getDefaultProps: function() {
        return {
            customer    : {},
            model       : {},
            onSubmit    : function(event) { Debugger.error('Nothing handled the form submit.')  },
            onChange    : function(event) { Debugger.error('Nothing handled the field change.') }
        }
    },

    render: function() {

        var handleChange = this.props.onChange;
        var customer     = this.props.customer;
        var disableEmail = (customer._id) ? true : false;
        var phoneType    = customer.primaryPhone.phoneType || 'Phone';
        var phoneLabel   = phoneType + ' #';

        return (
            React.createElement('form', { autocomplete: false, className: 'database-form', onSubmit: this.props.onSubmit },
                React.createElement('div', null,
                    React.createElement(Radix.Components.get('FormInputText'), { onChange: handleChange, name: 'customer:givenName', label: 'First Name', required: true, value: customer.givenName }),
                    React.createElement(Radix.Components.get('FormInputText'), { onChange: handleChange, name: 'customer:familyName', label: 'Last Name', required: true, value: customer.familyName })
                ),
                React.createElement('div', null,
                    React.createElement(Radix.Components.get('FormInputText'), { onChange: handleChange, type: 'email', name: 'customer:primaryEmail', label: 'Email Address', required: !disableEmail, readonly: disableEmail, value: customer.primaryEmail }),
                    React.createElement(Radix.Components.get('FormInputText'), { onChange: handleChange, type: 'tel', name: 'customer:primaryPhone', label: phoneLabel, value: customer.primaryPhone.number })
                ),
                React.createElement('div', null,
                    React.createElement(Radix.Components.get('FormInputText'), { onChange: handleChange, name: 'customer:companyName', label: 'Company Name', value: customer.companyName }),
                    React.createElement(Radix.Components.get('FormInputText'), { onChange: handleChange, name: 'customer:title', label: 'Job Title', value: customer.title })
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
            )
        )
    }
});
