React.createClass({ displayName: 'ComponentInquiry',

    getDefaultProps: function() {
        return {
            title           : 'Request More Information',
            className       : null,
            modelType       : null,
            modelIdentifier : null,
            enableNotify    : false,
            notifyEmail     : null,
        };
    },

    componentDidMount: function() {
        EventDispatcher.subscribe('CustomerManager.customer.loaded', function() {
            this.setState({ customer : CustomerManager.getCustomer() });
        }.bind(this));

        EventDispatcher.subscribe('CustomerManager.customer.unloaded', function() {
            this.setState({ customer : CustomerManager.getCustomer(), nextTemplate: null });
        }.bind(this));
    },

    getInitialState: function() {
        return {
            customer        : CustomerManager.getCustomer(),
            nextTemplate    : null
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

        var sourceKey = 'inquiry';
        var payload   = {
            data: data,
            meta: {
                model  : {
                    type       : this.props.modelType,
                    identifier : this.props.modelIdentifier
                },
                notify : {
                    enabled : this.props.enableNotify,
                    email   : this.props.notifyEmail
                }
            }
        };

        Debugger.info('InquiryModule', 'handleSubmit', sourceKey, payload);

        Ajax.send('/app/submission/' + sourceKey, 'POST', payload).then(function(response, xhr) {
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
            error.displayAjaxError(jqXHR);
        });
    },

    _formRefs: {},

    handleFieldRef: function(input) {
        if (input) {
            this._formRefs[input.props.name] = input;
        }
    },

    render: function() {
        Debugger.log('ComponentInquiry', 'render()', this);

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
                React.createElement(Radix.Components.get('ModalLinkLoginVerbose')),
                React.createElement('hr'),
                React.createElement(Radix.Forms.get('Inquiry'), {
                    customer     : this.state.customer,
                    onSubmit     : this.handleSubmit,
                    fieldRef     : this.handleFieldRef
                }),
                React.createElement(Radix.Components.get('FormErrors'), { ref: this._setErrorDisplay }),
                React.createElement(Radix.Components.get('FormLock'),   { ref: this._setLock })
            );
        }
        return (elements);
    },

    _setErrorDisplay: function(ref) {
        this._error = ref;
    },

    _setLock: function(ref) {
        this._formLock = ref;
    },

});
