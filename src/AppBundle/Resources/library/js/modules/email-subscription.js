function EmailSubscriptionModule()
{
    this.config = ClientConfig.values.modules.emailSubscription;

    this.module = React.createClass({ displayName: 'EmailSubscriptionModule',

        componentDidMount: function() {
            EventDispatcher.subscribe('CustomerManager.customer.loaded', function() {
                var customer = this.fillCustomer(CustomerManager.getCustomer());
                this.setState({ customer : customer });
            }.bind(this));

            EventDispatcher.subscribe('CustomerManager.customer.unloaded', function() {
                var customer = this.fillCustomer(CustomerManager.getCustomer());
                this.setState({ customer : customer, nextTemplate: null });
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
                title  : 'Manage Email Subscriptions',
                model  : {},
                notify : {
                    enabled : false,
                    email   : null
                }
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

            var sourceKey = 'email-subscription';
            var payload   = {
                data: this._formData,
                meta: {
                    model  : this.props.model,
                    notify : this.props.notify
                }
            };

            Ajax.send('/app/submission/' + sourceKey, 'POST', payload).then(function(response) {
                locker.unlock();

                // Refresh the customer, if logged in.
                if (CustomerManager.isLoggedIn()) {
                    CustomerManager.reloadCustomer();
                }

                // Set the next template to display (thank you page, etc).
                var template = (response.data) ? response.data.template || null : null;
                this.setState({ nextTemplate: template });

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
            return (
                React.createElement('div', { className: 'platform-element' },
                    React.createElement('h2', null, this.props.title),
                    this.getAuthElement(),
                    React.createElement('hr'),
                    React.createElement('div', { className: 'email-subscription-wrapper' },
                        React.createElement(Radix.Components.get('FormProductsEmail')),
                        React.createElement(Radix.Forms.get('EmailSubscription'), {
                            customer     : this.state.customer,
                            nextTemplate : this.state.nextTemplate,
                            onSubmit     : this.handleSubmit,
                            onChange     : this.handleChange
                        })

                    ),
                    React.createElement(Radix.Components.get('FormErrors'), { ref: this._setErrorDisplay }),
                    React.createElement(Radix.Components.get('FormLock'),   { ref: this._setLock })
                )
            )
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
