React.createClass({ displayName: 'FormInquiry',

    handleSubmit: function(event) {
        event.preventDefault();
    },

    render: function() {

        var customer     = this.props.customer;
        var disableEmail = (customer._id) ? true : false;
        var phoneType    = customer.primaryPhone.phoneType || 'Phone';
        var phoneLabel   = phoneType + ' #';

        return (
            React.createElement(Radix.Components.get('Form'), { autocomplete: false, onSubmit: this.handleSubmit },
                React.createElement('div', null,
                    React.createElement(Radix.Components.get('FormInputText'), { name: 'givenName', label: 'First Name', required: true, value: customer.givenName }),
                    React.createElement(Radix.Components.get('FormInputText'), { name: 'familyName', label: 'Last Name', required: true, value: customer.familyName })
                ),
                React.createElement("div", null,
                    React.createElement(Radix.Components.get('FormInputText'), { type: 'email', name: 'email', label: 'Email Address', required: !disableEmail, disabled: disableEmail, value: customer.primaryEmail }),
                    React.createElement(Radix.Components.get('FormInputText'), { type: 'text', name: 'phone', label: phoneLabel, value: customer.primaryPhone.number })
                )
            )
        )
    }
});
