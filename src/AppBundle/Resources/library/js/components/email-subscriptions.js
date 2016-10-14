React.createClass({ displayName: 'ComponentEmailSubscriptions',

    componentDidMount: function() {
        EventDispatcher.subscribe('CustomerManager.customer.loaded', function() {
            this.setState({ customer : CustomerManager.getCustomer() });
        }.bind(this));

        EventDispatcher.subscribe('CustomerManager.customer.unloaded', function() {
            this.setState({ customer : CustomerManager.getCustomer(), nextTemplate: null });
        }.bind(this));
    },

    getDefaultProps: function() {
        return {
            title     : 'Manage Email Subscriptions',
            className : null,
        };
    },

    getInitialState: function() {
        return {
            customer     : CustomerManager.getCustomer(),
            nextTemplate : null
        }
    },

    handleSubmit: function(event) {
        event.preventDefault();

        var locker = this._formLock;
        var error  = this._error;

        error.clear();
        locker.lock();

        var data = {};
        for (var name in this._formRefs) {
            var ref = this._formRefs[name];
            data[name] = ref.state.value;
        }

        data['submission:referringHost'] = window.location.protocol + '//' + window.location.host;
        data['submission:referringHref'] = window.location.href;

        var sourceKey = 'product-email-deployment-optin';
        var payload   = {
            data: data
        };

        Debugger.info('EmailSubscriptionModule', 'handleSubmit', sourceKey, payload);

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
            this.setState({ nextTemplate: template });

        }.bind(this), function(jqXHR) {
            locker.unlock();
            this._error.displayAjaxError(jqXHR);
        }.bind(this));
    },

    _formRefs: {},

    handleFieldRef: function(input) {
        if (input) {
            this._formRefs[input.props.name] = input;
        }
    },

    render: function() {
        Debugger.log('ComponentEmailSubscriptions', 'render()', this);

        var className = 'platform-element';
        if (this.props.className) {
            className = className + ' ' + this.props.className;
        }
        var elements;

        if (this.state.nextTemplate) {
            elements = React.createElement('div', { className: className, dangerouslySetInnerHTML: { __html: this.state.nextTemplate } });
        } else {
            elements = React.createElement('div', { className: className },
                React.createElement('h2', null, this.props.title),
                React.createElement(Radix.Components.get('ModalLinkLogin'), {
                    wrappingClass : 'muted',
                    prefix        : 'If you already have an account, you can',
                    label         : 'login',
                    suffix        : 'to speed up this request.'
                }),
                React.createElement('hr'),
                React.createElement('div', { className: 'email-subscription-wrapper' },
                    React.createElement(Radix.Components.get('FormProductsEmail'), {
                        fieldRef : this.handleFieldRef,
                        optIns   : this._getOptInsFor(this.state.customer.primaryEmail)
                    }),
                    React.createElement(Radix.Forms.get('EmailSubscription'), {
                        customer     : this.state.customer,
                        onSubmit     : this.handleSubmit,
                        fieldRef     : this.handleFieldRef
                    })
                ),
                React.createElement(Radix.Components.get('FormErrors'), { ref: this._setErrorDisplay }),
                React.createElement(Radix.Components.get('FormLock'),   { ref: this._setLock })
            );
        }
        return (elements);
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
