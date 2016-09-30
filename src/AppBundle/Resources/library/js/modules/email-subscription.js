function EmailSubscriptionModule()
{
    this.config = ClientConfig.values.modules.emailSubscription;

    this.module = React.createClass({ displayName: 'EmailSubscriptionModule',

        componentDidMount: function() {
            EventDispatcher.subscribe('CustomerManager.customer.loaded', function() {
                var customer = this.fillCustomer(CustomerManager.getCustomer());
                this.setState({ customer : customer, error: null });

            }.bind(this));

            EventDispatcher.subscribe('CustomerManager.customer.unloaded', function() {
                var customer = this.fillCustomer(CustomerManager.getCustomer());
                this.setState({ customer : customer, nextTemplate: null, error: null });
            }.bind(this));
        },

        fillCustomer: function(customer) {
            // @todo This should be handled by the backend...
            customer.answers        = customer.answers        || [];
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
                title  : 'Manage Email Subscriptions'
            };
        },

        getInitialState: function() {
            var customer = this.fillCustomer(CustomerManager.getCustomer());
            return {
                customer        : customer,
                error           : null,
                nextTemplate    : null
            }
        },

        handleSubmit: function(event) {
            event.preventDefault();

            Debugger.info('EmailSubscriptionModule', 'handleSubmit');

            var locker = this._formLock;

            locker.lock();

            this._formData['submission:referringHost'] = window.location.protocol + '//' + window.location.host;
            this._formData['submission:referringHref'] = window.location.href;

            var sourceKey = 'product-email-deployment-optin';
            var payload   = {
                data: this._formData
            };

            Ajax.send('/app/submission/' + sourceKey, 'POST', payload).then(function(response) {
                locker.unlock();

                // Refresh the customer, if logged in.
                if (CustomerManager.isLoggedIn()) {
                    CustomerManager.reloadCustomer().then(function() {
                        EventDispatcher.trigger('CustomerManager.customer.loaded');
                    });
                }

                // Set the next template to display (thank you page, etc).
                var template = (response.data) ? response.data.template || null : null;
                this.setState({ nextTemplate: template, error: null });

            }.bind(this), function(jqXHR) {
                locker.unlock();
                this._error.displayAjaxError(jqXHR);
            }.bind(this));
        },

        _formData: {},

        handleChange: function(event) {
            this._formData[event.target.name] = event.target.value;
        },

        render: function() {
            var element;
            if (this.state.nextTemplate) {
                element = React.createElement('div', { dangerouslySetInnerHTML: { __html: this.state.nextTemplate } });
            } else {
                element = this._getContents();
            }
            return (element);
        },

        _getContents: function() {

            return React.createElement('div', { className: 'platform-element' },
                React.createElement('h2', null, this.props.title),
                this.getAuthElement(),
                React.createElement('hr'),
                React.createElement('div', { className: 'email-subscription-wrapper' },
                    React.createElement(Radix.Components.get('FormProductsEmail'), {
                        onChange : this.handleChange,
                        optIns   : this._getPrimaryOptIns()
                    }),
                    React.createElement(Radix.Forms.get('EmailSubscription'), {
                        customer     : this.state.customer,
                        nextTemplate : this.state.nextTemplate,
                        onSubmit     : this.handleSubmit,
                        onChange     : this.handleChange
                    })
                ),
                React.createElement(Radix.Components.get('FormErrors'), { ref: this._setErrorDisplay }),
                React.createElement(Radix.Components.get('FormLock'),   { ref: this._setLock })
            );
        },

        _getPrimaryOptIns: function() {
            return this.state.customer.primaryOptIns.products;
        },

        _getOptInsFor: function(email) {
            for (var i = 0; i < this.state.customer.optIns.length; i++) {
                var optIn = this.state.customer.optIns[i];
                if (email === optIn.address) {
                    return optIn.products;
                }
            }
            return {};
        },

        _setErrorDisplay: function(ref) {
            this._error = ref;
        },

        _setLock: function(ref) {
            this._formLock = ref;
        },

    });

    this.getProps = function() {
        var jqObj = this.getTarget();
        if (!jqObj) {
            return {};
        }
        return {
            title  : jqObj.data('title') || 'Manage Email Subscriptions'
        };
    },

    this.getTarget = function() {
        var jqObj = $(this.config.target);
        return (jqObj.length) ? jqObj : undefined;
    },

    this.propsAreValid = function(props) {
        return true;
    },

    this.render = function() {
        var jqObj = this.getTarget();
        if (!jqObj) {
            // Element not present.
            Debugger.info('EmailSubscriptionModule', 'No target element found on page. Skipping render.');
            return;
        }

        var props = this.getProps();
        if (this.propsAreValid(props)) {
            React.render(
                React.createElement(this.module, props),
                jqObj[0]
            );
        }
    }
}
