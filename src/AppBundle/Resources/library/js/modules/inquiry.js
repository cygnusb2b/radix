function InquiryModule()
{
    this.config = ClientConfig.values.modules.inquiry;

    this.module = React.createClass({ displayName: 'InquiryModule',

        componentDidMount: function() {
            EventDispatcher.subscribe('CustomerManager.customer.loaded', function() {
                var customer = this.fillCustomer(CustomerManager.getCustomer());
                this.setState({ customer : customer });
            }.bind(this));

            EventDispatcher.subscribe('CustomerManager.customer.unloaded', function() {
                var customer = this.fillCustomer(CustomerManager.getCustomer());
                this.setState({ customer : customer });
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
                title  : 'Request More Information',
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
                customer    : customer,
                error       : null
            }
        },

        handleSubmit: function(event) {
            event.preventDefault();

            Debugger.info('InquiryModule', 'handleSubmit');

            var locker = this._formLock;

            locker.lock();

            this._formData['submission:referringUrl'] = window.location.protocol + '//' + window.location.host;

            var sourceKey = 'inquiry';
            var payload   = {
                data: this._formData,
                meta: {
                    model  : this.props.model,
                    notify : this.props.notify
                }
            };

            Ajax.send('/app/submission/' + sourceKey, 'POST', payload).then(function(response) {
                locker.unlock();
                // Show thank you page??
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
                    React.createElement(Radix.Forms.get('Inquiry'), {
                        customer    : this.state.customer,
                        onSubmit    : this.handleSubmit,
                        onChange    : this.handleChange
                    }),
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
