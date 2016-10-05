function InquiryModule()
{
    this.config = ClientConfig.values.modules.inquiry;

    this.module = React.createClass({ displayName: 'InquiryModule',

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
                customer        : customer,
                error           : null,
                nextTemplate    : null
            }
        },

        handleSubmit: function(event) {
            event.preventDefault();



            var locker = this._formLock;

            locker.lock();

            var data = {};
            for (var name in this._formRefs) {
                var ref = this._formRefs[name];
                data[name] = ref.state.value;
            }

            data['submission:referringHost'] = window.location.protocol + '//' + window.location.host;
            data['submission:referringHref'] = window.location.href;

            var sourceKey = 'inquiry';
            var payload   = {
                data: data,
                meta: {
                    model  : this.props.model,
                    notify : this.props.notify
                }
            };

            Debugger.info('InquiryModule', 'handleSubmit', sourceKey, payload);

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

        // @todo This is no longer needed, thanks to _formRefs
        _formData: {},

        _formRefs: {},

        // @todo This is no longer needed, due to handleFieldRef
        handleChange: function(event) {
            this._formData[event.target.name] = event.target.value;
        },

        handleFieldRef: function(input) {
            if (input) {
                this._formRefs[input.props.name] = input;
            }
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
                React.createElement(Radix.Forms.get('Inquiry'), {
                    customer     : this.state.customer,
                    nextTemplate : this.state.nextTemplate,
                    onSubmit     : this.handleSubmit,
                    onChange     : this.handleChange,
                    fieldRef     : this.handleFieldRef
                }),
                React.createElement(Radix.Components.get('FormErrors'), { ref: this._setErrorDisplay }),
                React.createElement(Radix.Components.get('FormLock'),   { ref: this._setLock })
            );
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
