function InquiryModule()
{
    this.config = ClientConfig.values.modules.inquiry;

    this.module = React.createClass({ displayName: 'InquiryModule',

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

        render: function() {
            return (
                React.createElement('div', { className: 'platform-element' },
                    React.createElement('h2', null, this.props.title),
                    this.getAuthElement(),
                    React.createElement('hr'),
                    React.createElement(Radix.Forms.get('Inquiry'), { customer: this.state.customer })
                )
            )
        }
    });

    this.getProps = function() {
        var jqObj = this.getTarget();
        if (!jqObj) {
            return {};
        }
        return {
            title  : jqObj.data('title') || 'Request More Information',
            model  : {
                type       : jqObj.data('model-type'),
                identifier : jqObj.data('model-identifier')

            },
            notify : {
                enabled : jqObj.data('enable-notify') || false,
                email   : jqObj.data('notify-email')  || null
            }
        };
    },

    this.getTarget = function() {
        var jqObj = $(this.config.target);
        return (jqObj.length) ? jqObj : undefined;
    },

    this.propsAreValid = function(props) {
        if (!props.model.type || !props.model.identifier) {
            Debugger.error('InquiryModule', 'No model-type or model-identifier data attribues found on the element. Unable to render.');
            return false;
        }
        return true;
    },

    this.render = function() {
        var jqObj = this.getTarget();
        if (!jqObj) {
            // Element not present.
            Debugger.info('InquiryModule', 'No target element found on page. Skipping render.');
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
