React.createClass({ displayName: 'FormInquiry',

    componentWillReceiveProps: function(props) {
        var address = props.customer.primaryAddress || {};
        if (address.countryCode) {
            this.setState({ country: address.countryCode });
        } else {
            this.setState({ country: null });
        }
    },

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
            locked  : false,
            country : this.props.customer.primaryAddress.countryCode
        };
    },

    getPostalCodeElement: function() {
        var element;
        var country  = this.state.country;
        var customer = this.props.customer;
        if ('USA' === country || 'CAN' === country) {
            element = React.createElement(Radix.Components.get('FormInputText'), { name: 'postalCode', label: 'Zip/Postal Code', value: customer.primaryAddress.postalCode });
        }
        return element;
    },

    handleCountryChange: function(event) {
        this.setState({ country: event.target.value });
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
                React.createElement('div', null,
                    React.createElement(Radix.Components.get('FormInputText'), { type: 'email', name: 'email', label: 'Email Address', required: !disableEmail, readonly: disableEmail, value: customer.primaryEmail }),
                    React.createElement(Radix.Components.get('FormInputText'), { type: 'tel', name: 'phone', label: phoneLabel, value: customer.primaryPhone.number })
                ),
                React.createElement('div', null,
                    React.createElement(Radix.Components.get('FormInputText'), { name: 'companyName', label: 'Company Name', value: customer.companyName }),
                    React.createElement(Radix.Components.get('FormInputText'), { name: 'title', label: 'Job Title', value: customer.title })
                ),
                React.createElement('div', null,
                    React.createElement(Radix.Components.get('FormSelectCountry'), { selected: this.state.country, onChange: this.handleCountryChange }),
                    this.getPostalCodeElement()
                ),
                React.createElement('div', null,
                    React.createElement(Radix.Components.get('FormQuestion'), { keyOrId: 'purchase-intent' }),
                    React.createElement(Radix.Components.get('FormQuestion'), { tagKeyOrId: 'business-code' }),
                    React.createElement(Radix.Components.get('FormQuestion'), { tagKeyOrId: 'title-code' }),
                    React.createElement(Radix.Components.get('FormQuestion'), { keyOrId: 'comments' })
                )
            )
        )
    }
});
