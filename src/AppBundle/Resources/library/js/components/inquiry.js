React.createClass({ displayName: 'ComponentInquiry',

    getDefaultProps: function() {
        return {
            title           : 'Request More Information',
            description     : null,
            className       : null,
            modelType       : null,
            modelIdentifier : null,
            notify          : {}, // Technically the notify value could be an array of notification objects.
            successRedirect : null,
            referringPath   : null
        };
    },

    componentDidMount: function() {
        EventDispatcher.subscribe('AccountManager.account.loaded', function() {
            this.setState({ account : AccountManager.getAccount() });
        }.bind(this));

        EventDispatcher.subscribe('AccountManager.account.unloaded', function() {
            this.setState({ account : AccountManager.getAccount(), nextTemplate: null });
        }.bind(this));
    },

    getInitialState: function() {
        return {
            account      : AccountManager.getAccount(),
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

        var referringHost = window.location.protocol + '//' + window.location.host;
        var referringHref = window.location.href;
        if (Utils.isString(this.props.referringPath)) {
            referringHref = referringHost + '/' + this.props.referringPath.replace(/^\//, '');
        }

        data['submission:referringHost'] = referringHost;
        data['submission:referringHref'] = referringHref;

        var sourceKey = 'inquiry';
        var payload   = {
            data: data,
            meta: {
                model  : {
                    type       : this.props.modelType,
                    identifier : this.props.modelIdentifier
                }
            },
            notify : Utils.isObject(this.props.notify) ? this.props.notify : {}
        };

        Debugger.info('InquiryModule', 'handleSubmit', sourceKey, payload);

        Ajax.send('/app/submission/' + sourceKey, 'POST', payload).then(function(response, xhr) {
          if (Utils.isString(this.props.successRedirect)) {
            // Redirect the user.
            window.location.href = this.props.successRedirect;
          } else {
            // Refresh the account, if logged in.
            if (AccountManager.isLoggedIn()) {
              AccountManager.reloadAccount().then(function() {
                EventDispatcher.trigger('AccountManager.account.loaded');
              });
            }
            // Set the next template to display.
            this.setState({ nextTemplate: template });
          }
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
                React.createElement('p', { dangerouslySetInnerHTML: { __html: this.props.description } }),
                React.createElement(Radix.Components.get('ModalLinkLoginVerbose')),
                React.createElement('hr'),
                React.createElement(Radix.Forms.get('Inquiry'), {
                    account  : this.state.account,
                    onSubmit : this.handleSubmit,
                    fieldRef : this.handleFieldRef
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
