React.createClass({ displayName: 'FormRegister',

    getDefaultProps: function() {
        return {
            customer    : {
                primaryAddress: {},
                primaryPhone: {}
            },
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
        var phoneType    = customer.primaryPhone.phoneType || 'Phone';
        var phoneLabel   = phoneType + ' #';


        return React.createElement('form', { autocomplete: false, className: 'database-form', onSubmit: this.props.onSubmit },
            React.createElement('div', null,
                React.createElement(Radix.Components.get('FormInputText'), { onChange: handleChange, name: 'customer:givenName', wrapperClass: 'givenName', label: 'First Name', required: true, value: customer.givenName }),
                React.createElement(Radix.Components.get('FormInputText'), { onChange: handleChange, name: 'customer:familyName', wrapperClass: 'familyName', label: 'Last Name', required: true, value: customer.familyName })
            ),
            React.createElement('div', null,
                React.createElement(Radix.Components.get('FormInputText'), { onChange: handleChange, type: 'email', name: 'customer:primaryEmail', wrapperClass: 'email', label: 'Email Address', required: true, value: customer.primaryEmail }),
                React.createElement(Radix.Components.get('FormInputText'), { onChange: handleChange, type: 'password', name: 'customer:password', wrapperClass: 'password', label: 'Password', value: null })
            ),
            React.createElement('div', null,
                React.createElement(Radix.Components.get('FormInputText'), { onChange: handleChange, name: 'customer:companyName', wrapperClass: 'companyName', label: 'Company Name', value: customer.companyName }),
                React.createElement(Radix.Components.get('FormInputText'), { onChange: handleChange, name: 'customer:title', wrapperClass: 'title', label: 'Job Title', value: customer.title })
            ),
            React.createElement('div', null,
                React.createElement(Radix.Components.get('CountryPostalCode'), { onChange: handleChange, postalCode: customer.primaryAddress.postalCode, countryCode: customer.primaryAddress.countryCode })
            ),
            React.createElement('div', null,
                React.createElement(Radix.Components.get('FormQuestion'), { onChange: handleChange, tagKeyOrId: 'business-code', answers: customer.answers }),
                React.createElement(Radix.Components.get('FormQuestion'), { onChange: handleChange, tagKeyOrId: 'title-code', answers: customer.answers })
            ),
            React.createElement('button', { type: 'submit'}, 'Sign Up')
        );
    }
});
