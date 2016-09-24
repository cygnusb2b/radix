React.createClass({ displayName: 'FormInquiry',

    buildForm: function() {
        var customer     = this.state.customer;
        var disableEmail = (customer._id) ? true : false;
        var phoneLabel   = customer.primaryPhone.phoneType + ' #';

        return React.createElement('fieldset', null,
            React.createElement('div', null,
                React.createElement(Radix.Components.get('FormInputText'), { name: 'givenName', label: 'First Name', required: true, value: customer.givenName }),
                React.createElement(Radix.Components.get('FormInputText'), { name: 'familyName', label: 'Last Name', required: true, value: customer.familyName })
            ),
            React.createElement("div", null,
                React.createElement(Radix.Components.get('FormInputText'), { type: 'email', name: 'email', label: 'Email Address', required: !disableEmail, disabled: disableEmail, value: customer.primaryEmail }),
                React.createElement(Radix.Components.get('FormInputText'), { type: 'text', name: 'phone', label: phoneLabel, value: customer.primaryPhone.number })
            )
        );
    },

    componentDidMount: function() {
        EventDispatcher.subscribe('CustomerManager.customer.loaded', function() {
            var customer = this.fillCustomer(CustomerManager.getCustomer());
            this.setState({
                customer    : customer,
                countryCode : customer.primaryAddress.countryCode || null
            });
        }.bind(this));

        EventDispatcher.subscribe('CustomerManager.customer.unloaded', function() {
            this.setState({
                customer    : this.fillCustomer(CustomerManager.getCustomer()),
                countryCode : null
            });
        }.bind(this));
    },

    fillCustomer: function(customer) {
        customer.primaryAddress = customer.primaryAddress || {};
        customer.primaryPhone   = customer.primaryPhone   || {};
        return customer;
    },

    getAuthElement: function() {
        var element;
        if (!this.state.customer._id) {
            element = React.createElement("p", {className: "muted"}, "If you already have an account, you can ", React.createElement("a", {style: {cursor:"pointer"}, onClick: Radix.SignIn.login}, "login"), " to speed up this request.");
        }
        return element;
    },

    getDefaultProps: function() {
        return {
            title  : 'Request More Information',
            model  : {},
            notify : {
                enabled : false,
                email   : null
            }
        };
    },

    getInitialState: function() {
        // @todo This should be handled by the backend...
        var customer = this.fillCustomer(CustomerManager.getCustomer());
        return {
            customer     : customer,
            countryCode  : customer.primaryAddress.countryCode || null,
            errorMessage : null
        }
    },

    handleSubmit: function(event) {
        event.preventDefault();
    },

    render: function() {
        return (
            React.createElement('div', { className: 'platform-element' },
                React.createElement('h2', null, this.props.title),
                this.getAuthElement(),
                React.createElement('hr'),
                React.createElement(Radix.Components.get('Form'), { autocomplete: false, onSubmit: this.handleSubmit },
                    this.buildForm()
                )
            )
        )
    }
});
