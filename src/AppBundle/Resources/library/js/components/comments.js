React.createClass({ displayName: 'ComponentComments',

    _loadComments: function() {
        if (this.state.settings.enabled) {
            this.setState({ loading: true });
            Ajax.send('/app/posts/comment/' + encodeURIComponent(this.props.streamId) + '/' + this.state.page, 'GET').then(function(response) {
                this.setState({ comments: response.data.posts, stream: response.data.stream, loading: false, count: response.data.posts.length });
            }.bind(this), function(jqXHR) {
                this.setState({ error: jqXHR, loading: false })
            }.bind(this));
        }
    },

    _loadMoreComments: function() {
        var page = this.state.page + 1;
        var count = this.state.count;
        var comments = this.state.comments.slice();

        Ajax.send('/app/posts/comment/' + encodeURIComponent(this.props.streamId) + '/' + page, 'GET').then(function(response) {
            response.data.posts.forEach(function(post) {
                comments.push(post);
            });
            this.setState({ comments: comments, count: count + response.data.posts.length, page: page });
        }.bind(this), function(jqXHR) {
            this.setState({ error: jqXHR })
        }.bind(this));
    },

    _pushNewComment: function(comment) {
        var comments = this.state.comments.slice();
        comments.unshift(comment);
        this.setState({ comments: comments });
    },

    componentDidMount: function() {
        this._loadComments();
        EventDispatcher.subscribe('AccountManager.account.loaded', function() {
            var account = AccountManager.getAccount();
            this.setState({ account : account, loggedIn : true, displayName: account.displayName || null });
        }.bind(this));

        EventDispatcher.subscribe('AccountManager.account.unloaded', function() {
            this.setState({ account : {}, loggedIn : false, displayName: null });
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
            displayName : AccountManager.getAccount().displayName || null,
            settings : Application.settings.posts,
            loading     : true,
            loadingMore : false,
            page     : 1,
            count    : 0,
            comments : [],
            stream   : {},
            error    : null,
        }
    },

    _requiresCaptcha: function() {
        var settings = this.state.settings;
        return !settings.requireAccount && settings.requireCaptcha;
    },

    handleSubmit: function(event) {
        event.preventDefault();

        var locker  = this._formLock;
        var error   = this._error;
        var captcha = this._requiresCaptcha() ? this._captcha.getResponse() : '';

        error.clear();

        if (this._requiresCaptcha() && !captcha) {
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
            this.setState({ displayName: response.data.displayName });
            this._pushNewComment(response.data);
            this._captcha.reset();
        }.bind(this), function(jqXHR) {
            locker.unlock();
            this._error.displayAjaxError(jqXHR);
            this._captcha.reset();
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
        var className = 'platform-element comments-container';
        if (this.props.className) {
            className = className + ' ' + this.props.className;
        }
        if (!this.state.settings.enabled) {
            return (undefined);
        }

        var form = React.createElement(Radix.Forms.get('Comment'), {
            className      : 'comment-form',
            display        : this._canComment() && !this.state.loading,
            allowAnonymous : this.state.settings.allowAnonymous,
            requireCaptcha : this._requiresCaptcha(),
            displayName    : this.state.displayName,
            fieldRef       : this.handleFieldRef,
            onSubmit       : this.handleSubmit,
            captchaRef     : this.handleCaptcha,
        });

        var comments, loadingDisplay, loadMore;
        if (this.state.loading) {
            loadingDisplay = React.createElement('p', { className: 'muted' }, 'Loading comments, please wait...');
        } else {
            comments = this.state.comments.map(function(comment, index) {
                comment.key = comment._id;
                return React.createElement(Radix.Components.get('Comment'), comment);
            });
            if (!comments.length) {
                comments = React.createElement('p', null, 'No comments have been added yet. Want to start the conversation?');
            }
            if (this.state.count >= this.state.settings.pageSize * this.state.page) {
                // @todo This doesn't take into consideration comments that were added by the current user, or others, for that matter.
                // This really needs to handled real time...
                loadMore = React.createElement('button', { onClick: this._loadMoreComments }, 'Load older comments...');
            }
        }

        //@TODO make this selector configurable.
        var check = 'platformCommentsCount';
        if (null !== document.getElementById(check)) {
            // update detached count if item is found
            var identifier = document.getElementById(check).getAttribute('data-identifier');
            if (identifier) {
                document.getElementById(check).innerHTML = this.state.count;
            } else {
                Debugger.error('CommentComponent: No `identifier` data attribute found on `#'+check+'`!');
            }
        } else {
            Debugger.warn('CommentComponent: Could not find comments.detachedCount.bindTarget #`'+check+'`.');
        }

        return (
            React.createElement('div', { className: className },
                React.createElement('h3', null, this.props.title),
                React.createElement('hr'),
                loadingDisplay,
                this._getLoginLinks(),
                form,
                React.createElement(Radix.Components.get('FormErrors'), { ref: this._setErrorDisplay }),
                React.createElement('hr'),
                React.createElement('div', { className: 'comments' }, comments),
                loadMore,
                React.createElement('hr'),
                React.createElement(Radix.Components.get('FormLock'),   { ref: this._setLock })
            )
        );
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
