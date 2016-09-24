React.createClass({ displayName: 'FormInquiry',

    handleSubmit: function(event) {
        event.preventDefault();

        this.setState({ locked: true });

        Ajax.send('/app/auth', 'GET').then(function(response) {
            this.props.onSuccess(response);
            this.setState({ locked: false });
        }.bind(this), function(jqXHR) {
            this.props.onFailure(response);
            this.setState({ locked: false });
        }.bind(this));
    },

    getDefaultProps: function() {
        return {
            customer   : {},
            model      : {},
            onSuccess  : function(response) { },
            onFailure  : function(jqXHR) { }
        }
    },

    getInitialState: function() {
        return {
            locked: false
        };
    },

    render: function() {

        var customer     = this.props.customer;
        var disableEmail = (customer._id) ? true : false;
        var phoneType    = customer.primaryPhone.phoneType || 'Phone';
        var phoneLabel   = phoneType + ' #';

        return (
            React.createElement(Radix.Components.get('Form'), { locked: this.state.locked, autocomplete: false, onSubmit: this.handleSubmit },
                React.createElement('div', null,
                    React.createElement(Radix.Components.get('FormInputText'), { name: 'givenName', label: 'First Name', required: true, value: customer.givenName }),
                    React.createElement(Radix.Components.get('FormInputText'), { name: 'familyName', label: 'Last Name', required: true, value: customer.familyName })
                ),
                React.createElement("div", null,
                    React.createElement(Radix.Components.get('FormInputText'), { type: 'email', name: 'email', label: 'Email Address', required: !disableEmail, readonly: disableEmail, value: customer.primaryEmail }),
                    React.createElement(Radix.Components.get('FormInputText'), { type: 'tel', name: 'phone', label: phoneLabel, value: customer.primaryPhone.number })
                )
            )
        )
    }
});
