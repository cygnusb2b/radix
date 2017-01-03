React.createClass({ displayName: 'ComponentComments',

    componentDidMount: function() {
        EventDispatcher.subscribe('AccountManager.account.loaded', function() {
            var account = AccountManager.getAccount();
            this.setState({ account : account, loggedIn : true });
        }.bind(this));

        EventDispatcher.subscribe('AccountManager.account.unloaded', function() {
            var account = AccountManager.getAccount();
            this.setState({ account : account, loggedIn : false });
        }.bind(this));
    },

    getDefaultProps: function() {
        return {
            title       : 'Join the conversation!',
            streamId    : null, // The unique stream identifier.
            streamTitle : null,
            streamUrl   : window.location.href,
            className   : null,
        };
    },

    getInitialState: function() {
        return {
            loggedIn : AccountManager.isLoggedIn(),
            account  : AccountManager.getAccount(),
            settings : Application.settings.posts,
        }
    },

    handleSubmit: function(event) {
        event.preventDefault();

        var locker  = this._formLock;
        var error   = this._error;
        var captcha = this._captcha.getResponse() || '';

        error.clear();

        if (!captcha) {
            error.display('Please complete the reCaptcha before submitting the form.');
            return;
        }

        locker.lock();

        var data = {
            captcha : captcha,
            stream  : {
                identifier : this.props.streamId,
                title      : this.props.streamTitle,
                url        : this.props.streamUrl, // need to find a better way to get the URL so it can't be injected
            }
        };
        for (var name in this._formRefs) {
            var ref = this._formRefs[name];
            data[name] = ref.state.value;
        }

        var payload   = {
            data: data
        };

        Debugger.info('ComponentComments', 'handleSubmit', payload);

        Ajax.send('/app/posts/comment', 'POST', payload).then(function(response) {
            locker.unlock();
        }.bind(this), function(jqXHR) {
            locker.unlock();
            this._error.displayAjaxError(jqXHR);
        }.bind(this));
    },

    _formRefs: {},

    _captcha: {},
    handleCaptcha: function(captcha) {
        this._captcha = captcha;
    },

    handleFieldRef: function(input) {
        if (input) {
            this._formRefs[input.props.name] = input;
        }
    },

    render: function() {
        Debugger.log('ComponentComments', 'render()', this);

        var className = 'platform-element';
        if (this.props.className) {
            className = className + ' ' + this.props.className;
        }
        var elements;

        if (this.state.settings.enabled) {
            elements = React.createElement('div', { className: className },
                React.createElement('h2', null, this.props.title),
                this._getLoginLinks(),
                React.createElement('hr'),
                React.createElement(Radix.Forms.get('Comment'), {
                    display        : this._canComment(),
                    allowAnonymous : this.state.settings.allowAnonymous,
                    requireCaptcha : this.state.settings.requireCaptcha,
                    displayName    : this.state.account.displayName || null,
                    fieldRef       : this.handleFieldRef,
                    onSubmit       : this.handleSubmit,
                    captchaRef     : this.handleCaptcha,
                }),
                React.createElement(Radix.Components.get('FormErrors'), { ref: this._setErrorDisplay }),
                React.createElement(Radix.Components.get('FormLock'),   { ref: this._setLock })
            );
        }
        return (elements);
    },

    /**
     * Determines, based on the current state, if comments can be submitted.
     * If an account is required to comment, this will be return true.
     * Otherwise, the value will be based on whether an account is currently logged in.
     */
    _canComment: function() {
        if (!this.state.settings.requireAccount) {
            return true;
        }
        return this.state.loggedIn;
    },

    /**
     * Gets the login/register link elements, if required.
     * Links will only display if the current state does NOT allow comment submissions.
     *
     */
    _getLoginLinks: function() {
        var elements;

        if (!this._canComment()) {
            elements = React.createElement('p', null,
                React.createElement(Radix.Components.get('ModalLinkLogin'), {
                    wrappingTag : 'span',
                    prefix      : 'This site requires you to',
                    label       : 'login',
                    suffix      : 'or ',
                }),
                React.createElement(Radix.Components.get('ModalLinkRegister'), {
                    wrappingTag : 'span',
                    label       : 'register',
                    suffix      : 'to post a comment.',
                })
            );
        }
        return elements;
    },

    _setErrorDisplay: function(ref) {
        this._error = ref;
    },

    _setLock: function(ref) {
        this._formLock = ref;
    },

});
