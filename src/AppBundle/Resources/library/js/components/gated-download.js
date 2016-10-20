React.createClass({ displayName: 'ComponentGatedDownload',
    // @todo Should gating simply allow any set of "extra metadata", as opposed to requiring properties to be directly set?
    getDefaultProps: function() {
        return {
            title           : 'Download',
            description     : 'To access this piece of premium content, please verify that the questions below have been answered and are accurate.',
            fileUrl         : null, // The file to download on submit
            webhookUrl      : null, // The webhook url to request to retrieve the file (in case you want to hide the url from the component/frontend): note, the hook must utilize CORs
            className       : null,
            enableNotify    : false,
            notifyEmail     : null, // Notifications should be abstracted and (optionally) be handled by all components
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
        data['submission:fileUrl'] = this.props.fileUrl;

        var sourceKey = 'gated-download';
        var payload   = {
            data: data,
            meta: this.props.meta || {}
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
            // Redirect.
            window.location.replace(this.props.fileUrl);
            // this.setState({ nextTemplate: template });

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
        Debugger.log('ComponentGatedDownload', 'render()', this);

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
                React.createElement('p', null, this.props.description),
                React.createElement(Radix.Components.get('ModalLinkLoginVerbose')),
                React.createElement('hr'),
                React.createElement(Radix.Forms.get('GatedDownload'), {
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
