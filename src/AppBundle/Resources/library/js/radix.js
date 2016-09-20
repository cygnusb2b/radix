;
(function(Radix, undefined) {

    'use strict';
    var auth0;

    // Private properties
    var Debugger        = new Debugger();
    var Ajax            = new Ajax();
    // var ServerConfig    = new ServerConfig(hostname, serverConfig);
    // var ClientConfig    = new ClientConfig();
    var EventDispatcher = new EventDispatcher();
    var Callbacks       = new Callbacks();

    var ComponentLoader;
    var CustomerManager;
    var LibraryLoader;

    Radix.ajaxSend = function(url, method, payload, headers) {
        return Ajax.sendForm(url, method, payload, headers);
    };

    Radix.init = function() {
        console.info('Init me!!');
        // ComponentLoader = new ComponentLoader();
        // CustomerManager = new CustomerManager();
        // LibraryLoader   = new LibraryLoader();
    };

    Radix.on = function(key, callback) {
        EventDispatcher.subscribe(key, callback);
    };

    Radix.emit = function(key) {
        EventDispatcher.trigger(key);
    };

    Radix.registerCallback = function(key, callback) {
        Callbacks.register(key, callback);
    };

    // Public properties


    // Public methods
    Radix.setDebug = function(bit) {
        bit = Boolean(bit);
        if (true === bit) { Debugger.enable(); } else { Debugger.disable(); }
        return Radix;
    };

    Radix.hasCustomer = function() {
        return CustomerManager.isLoggedIn();
    };

    Radix.getCustomer = function() {
        return CustomerManager.getCustomer();
    };

    function Callbacks() {

        var registered = {};

        this.register = function(key, callback) {
            if ('function' === typeof callback) {
                registered[key] = callback;
            }
        };

        this.get = function (key) {
            if (registered.hasOwnProperty(key)) {
                return registered[key];
            }
            return undefined;
        };

        this.has = function(key) {
            return 'undefined' !== typeof this.get(key);
        };
    }

    function LibraryLoader() {

        var count = 0,
            libraries = [
                '//cdnjs.cloudflare.com/ajax/libs/react/0.13.0/react.min.js',
                'http://rsvpjs-builds.s3.amazonaws.com/rsvp-latest.min.js',
                '//cdn.auth0.com/w2/auth0-6.js'
            ];

        function loadLibraries() {
            for (var i = 0; i < libraries.length; i++) {
                Debugger.info('Loading library ' + libraries[i]);
                $.getScript(libraries[i]).then(function() {
                    if ('function' === typeof Auth0) {
                        auth0 = new Auth0({
                            domain: ServerConfig.values.external_libraries.auth0.domain,
                            clientID: ServerConfig.values.external_libraries.auth0.client_id,
                            callbackOnLocationHash: true
                        });
                    }
                    count = count + 1;
                    if (count >= libraries.length) {
                        EventDispatcher.trigger('ready')
                    }
                }).fail(function() {
                    Debugger.error('Required library could not be loaded!');
                })
            };
        }

        loadLibraries();
    }

    function SignInComponent() {

        var target = 'platformCustomerModal';

        var DatabaseRegister = React.createClass({displayName: "DatabaseRegister",
            getInitialState: function() {
                return {
                    errorMessage: null,
                    data: []
                }
            },

            handleSubmit: function(e) {
                e.preventDefault();
                var payload = {
                    firstName: React.findDOMNode(this.refs.firstName).value.trim(),
                    lastName: React.findDOMNode(this.refs.lastName).value.trim(),
                    companyName: React.findDOMNode(this.refs.companyName).value.trim(),
                    title: React.findDOMNode(this.refs.title).value.trim(),
                    password: React.findDOMNode(this.refs.password).value,
                    confirmPassword: React.findDOMNode(this.refs.confirmPassword).value,
                    email: React.findDOMNode(this.refs.email).value,
                    confirmEmail: React.findDOMNode(this.refs.confirmEmail).value,
                    displayName: React.findDOMNode(this.refs.displayName).value.trim(),
                };

                if (payload.password) {
                    if (payload.password.length < 4) {
                        this.setState({errorMessage: 'Password must be at least 4 characters!'});
                        return false;
                    } else if (payload.password.length > 4096) {
                        this.setState({errorMessage: 'Password must be less than 4096 characters!'});
                        return false;
                    } else if (payload.password !== payload.confirmPassword) {
                        this.setState({errorMessage: 'Passwords must match!'});
                        return false;
                    }
                }

                if (payload.email !== payload.confirmEmail) {
                    this.setState({errorMessage: 'Emails must match!'});
                    return false;
                }

                CustomerManager.databaseRegister(payload);

                React.findDOMNode(this.refs.password).value = '';
                React.findDOMNode(this.refs.confirmPassword).value = '';
            },

            componentDidMount: function() {
                EventDispatcher.subscribe('CustomerManager.register.submit', function() {
                    this.setState({errorMessage: null});
                }.bind(this));

                EventDispatcher.subscribe('CustomerManager.register.failure', function (e, parameters) {
                    this.setState({errorMessage: parameters});
                }.bind(this));
            },

            render: function() {
                return (
                    React.createElement("form", {className: "databaseForm", onSubmit: this.handleSubmit},
                        React.createElement("div", {className: ""},
                            React.createElement("h4", {className: "text-center"}, "OR")
                        ),
                        React.createElement("div", {className: ""},
                            React.createElement("div", {className: ""},
                                React.createElement("label", {htmlFor: "firstName", className: ""}, "First Name"),
                                React.createElement("input", {name: "firstName", ref: "firstName", type: "text", id: "firstName", className: "", placeholder: "First Name", required: "required", autofocus: "autofocus"})
                            ),
                            React.createElement("div", {className: ""},
                                React.createElement("label", {htmlFor: "lastName", className: ""}, "Last Name"),
                                React.createElement("input", {name: "lastName", ref: "lastName", type: "text", id: "lastName", className: "", placeholder: "Last Name", required: "required", autofocus: "autofocus"})
                            )
                        ),
                        React.createElement("div", {className: ""},
                            React.createElement("div", {className: ""},
                                React.createElement("label", {htmlFor: "companyName", className: ""}, "Company Name"),
                                React.createElement("input", {name: "companyName", ref: "companyName", type: "text", id: "companyName", className: "", placeholder: "Company Name", autofocus: "autofocus"})
                            ),
                            React.createElement("div", {className: ""},
                                React.createElement("label", {htmlFor: "title", className: ""}, "Title"),
                                React.createElement("input", {name: "title", ref: "title", type: "text", id: "title", className: "", placeholder: "Title", autofocus: "autofocus"})
                            )
                        ),
                        React.createElement("div", {className: ""},
                            React.createElement("div", {className: ""},
                                React.createElement("label", {htmlFor: "email", className: ""}, "Email Address"),
                                React.createElement("input", {name: "email", ref: "email", type: "email", id: "email", className: "", placeholder: "Email Address", required: "required", autofocus: "autofocus"})
                            ),
                            React.createElement("div", {className: ""},
                                React.createElement("label", {htmlFor: "confirmEmail", className: ""}, "Confirm Email Address"),
                                React.createElement("input", {name: "confirmEmail", ref: "confirmEmail", type: "email", id: "confirmEmail", className: "", placeholder: "Confirm Email Address", required: "required", autofocus: "autofocus"})
                            )
                        ),
                        React.createElement("div", {className: ""},
                            React.createElement("div", {className: ""},
                                React.createElement("label", {htmlFor: "password", className: ""}, "Password"),
                                React.createElement("input", {name: "password", ref: "password", type: "password", id: "password", className: "", placeholder: "Password", required: "required"})
                            ),
                            React.createElement("div", {className: ""},
                                React.createElement("label", {htmlFor: "confirmPassword", className: ""}, "Confirm Password"),
                                React.createElement("input", {name: "confirmPassword", ref: "confirmPassword", type: "password", id: "confirmPassword", className: "", placeholder: "Confirm Password", required: "required"})
                            )
                        ),
                        React.createElement("div", {className: ""},
                            React.createElement("div", {className: ""},
                                React.createElement("label", {htmlFor: "displayName", className: ""}, "Display Name"),
                                React.createElement("input", {name: "displayName", ref: "displayName", type: "text", id: "displayName", className: "", placeholder: "Display Name (when posting)", required: "required"})
                            )
                        ),
                        React.createElement("p", {className: "error text-danger"}, this.state.errorMessage),
                        React.createElement("div", {className: ""},
                            React.createElement("div", {className: ""},
                                React.createElement("button", {className: "", type: "submit"}, "Sign Up"),
                                React.createElement("p", {className: "text-center muted"}, "Already have an account? ", React.createElement("a", {href: "javascript:void(0)", onClick: PlatformComponents.SignIn.login}, "Sign in"), " .")
                            )
                        )

                    )
                );
            }
        });

        var RegisterContainer = React.createClass({displayName: "RegisterContainer",
            render: function() {
                var providerNodes = ServerConfig.values.customer.auth.map(function(key, index) {
                    if ('auth0' == key) {
                        return (
                            React.createElement(SocialLogin, {key: index})
                        );
                    }
                    return (
                        React.createElement(DatabaseRegister, {key: index})
                    );
                });
                return (
                    React.createElement("div", {className: "register"},
                        React.createElement("p", {className: "error text-danger"}),
                        React.createElement("h2", {className: "name"}, "Sign Up"),
                        providerNodes
                    )
                );
            }
        });

        var SocialButton = React.createClass({displayName: "SocialButton",
            getInitialState: function() {
                return {
                    data: {
                        class: 'none',
                        label: 'None'
                    }
                }
            },
            handleLogin: function(e) {
                e.preventDefault();
                CustomerManager.socialLogin(this.props.data.key);
            },
            render: function() {
                return (
                    React.createElement("button", {className: "social sign-in " + this.props.data.class, onClick: this.handleLogin},
                        // React.createElement("i", {className: "fa fa-"+this.props.data.class}),
                        React.createElement("span", null, "Login with "+this.props.data.label)
                    )
                );
            }
        });

        var SocialLogin = React.createClass({displayName: "SocialLogin",
            getInitialState: function() {
                return {
                    data: {
                        providers: ServerConfig.values.customer.social_providers
                    }
                }
            },
            render: function() {
                var socialProviderButtons = this.state.data.providers.map(function(provider, index) {
                    return (
                        React.createElement("div", {className: ""},
                            React.createElement(SocialButton, {key: index, data: provider})
                        )
                    );
                });
                return (
                    React.createElement("form", {className: "auth0Form"},
                        React.createElement("div", {className: ""},
                            socialProviderButtons
                        )
                    )
                );
            }
        });

        var DatabaseLogin = React.createClass({displayName: "DatabaseLogin",
            getInitialState: function() {
                return {
                    data: {}
                }
            },
            handleSubmit: function(e) {
                e.preventDefault();
                var profile = {
                    _username: React.findDOMNode(this.refs.email).value.trim(),
                    _password: React.findDOMNode(this.refs.password).value
                };

                if (!profile._username || !profile._password) {
                  return;
                }

                CustomerManager.databaseLogin(profile);

                // React.findDOMNode(this.refs.email).value = ''; // Do not reset email.
                React.findDOMNode(this.refs.password).value = '';
            },

            render: function() {
                return (
                    React.createElement("form", {className: "databaseForm", onSubmit: this.handleSubmit},
                        React.createElement("div", {className: ""},
                        React.createElement("div", {className: ""},
                            React.createElement("div", {className: ""},
                                React.createElement("label", {htmlFor: "inputEmail", className: ""}, "Email address"),
                                React.createElement("input", {type: "email", ref: "email", id: "inputEmail", className: "", placeholder: "* Email address", required: "required", autofocus: "autofocus"})
                            ),
                            React.createElement("div", {className: ""},
                                React.createElement("label", {htmlFor: "inputPassword", className: ""}, "Password"),
                                React.createElement("input", {type: "password", ref: "password", id: "inputPassword", className: "", placeholder: "* Password", required: "required"})
                            )
                        ),
                        React.createElement("div", {className: ""},
                            React.createElement("div", {className: ""},
                                React.createElement("button", {className: "", type: "submit"}, "Sign In"),
                                React.createElement("p", {className: "text-center muted"}, "Forgot your password? ", React.createElement("a", {href: "/reg/forgot_password/display"}, "Reset it"), "."),
                                React.createElement("p", {className: "text-center muted"}, "Need an account? ", React.createElement("a", {href: "/reg/register/display"}, "Sign up"), ".")
                                )
                            )
                        )
                    )
                );
            }
        });

        var LoginContainer = React.createClass({displayName: "LoginContainer",
            getInitialState: function() {
                return {
                    data: [],
                    errorMessage: null
                };
            },

            componentDidMount: function() {
                EventDispatcher.subscribe('CustomerManager.login.submit', function() {
                    this.setState({errorMessage: null});
                }.bind(this));

                EventDispatcher.subscribe('CustomerManager.login.failure', function (e, parameters) {
                    this.setState({errorMessage: parameters});
                }.bind(this));
            },

            render: function() {
                var providerNodes = ServerConfig.values.customer.auth.map(function(key, index) {
                    if ('auth0' === key) {
                        return (
                            React.createElement(SocialLogin, {key: index})
                        );
                    }
                    return (
                        React.createElement(DatabaseLogin, {key: index})
                    );
                });
                return (
                    React.createElement("div", {className: "login-list"},
                        React.createElement("h2", {className: "name"}, "Log In"),
                        providerNodes,
                        React.createElement("p", {className: "error text-danger"}, this.state.errorMessage)
                    )
                );
            }
        });

        var LogoutContainer = React.createClass({displayName: "LogoutContainer",
            render: function() {
                return (
                    React.createElement("div", {className: "login"},
                        React.createElement("h2", {className: "name"}, "You are currently logged in."),
                        React.createElement("p", null, React.createElement("a", {href: "javascript:void(0)", onClick: PlatformComponents.SignIn.logout}, "Logout"))
                    )
                );
            }
        });

        var CustomerContainer = React.createClass({displayName: "CustomerContainer",

            getInitialState: function() {
                return {
                    registering: false
                };
            },

            componentDidMount: function() {

                EventDispatcher.subscribe('SignIn.login', function() {
                    this.setState({registering: false});
                    this.show();
                }.bind(this));

                EventDispatcher.subscribe('SignIn.register', function(e) {
                    if (true === e.isDefaultPrevented()) {
                        return;
                    }
                    this.setState({registering: true});
                    this.show();
                }.bind(this));

                EventDispatcher.subscribe('CustomerManager.login.success', function() {
                    this.hide();
                }.bind(this));
            },

            show: function() {
                $('#'+target).show();
            },

            hide: function() {
                $('#'+target).hide();
            },

            render: function() {
                if (true == ServerConfig.values.customer.enabled) {
                    var contents;
                    if (false === CustomerManager.isLoggedIn()) {
                        if (false === this.state.registering) {
                            contents = React.createElement(LoginContainer, null);
                        } else {
                            contents = React.createElement(RegisterContainer, null);
                        }
                    } else {
                        contents = React.createElement(LogoutContainer, null);
                    }
                    if (target === 'platformCustomerModal') {
                        return (
                            React.createElement("div", {className: "login page wrap"},
                                React.createElement("button", {onClick: this.hide, className: "dismiss"}, "Close"),
                                contents
                            )
                        );
                    }
                    return (
                        React.createElement("div", {className: "login page wrap"},
                            contents
                        )
                    );
                } else {
                    Debugger.error('PlatformJS: Customer component is not enabled.');
                }
            }
        });

        this.render = function() {
            setButtonState();
            bindButtonEvents();

            EventDispatcher.subscribe('CustomerManager.customer.loaded', setButtonState);
            EventDispatcher.subscribe('CustomerManager.customer.unloaded', setButtonState);

            /**
             * Include CSS as style tag so it can be overwritten by the calling site as needed.
             */
            var style = $('<style type="text/css">'+
                '@import "//s3.amazonaws.com/cygnusimages/base/components/pcfa.min.css";.platformComments,.platformComments .children{clear:both}#platformCustomerModal,.platformComments{font-family:arial,sans-serif;color:#24282c}#platformCustomerModal a,#platformCustomerModal a:hover,#platformCustomerModal a:visited,.platformComments a,.platformComments a:hover,.platformComments a:visited{color:#323a45}#platformCustomerModal{position:fixed;top:0;right:0;bottom:0;left:0;z-index:99999;display:none;overflow-y:auto;-webkit-user-select:none;-moz-user-select:none;-ms-user-select:none;user-select:none;background:#eee;background:rgba(238,238,238,.85);-webkit-tap-highlight-color:transparent;-webkit-touch-callout:none}#platformCustomerModal .wrap{position:relative;width:60%;padding:10px;margin:60px auto;background:#fafafa;box-shadow:0 2px 5px 0 rgba(0,0,0,.2)}.platformComments .comment{position:relative;float:left;width:calc(100% - 1.77778em);margin:.88889em}#platformCustomerModal button,.platformComments button{height:initial;position:relative;display:block;width:100%;padding:.88889em;margin:.25253em auto;font-size:.88889em;cursor:pointer;transition:all .2s cubic-bezier(.4,0,.2,1);transition-delay:.2s;text-align:center;text-decoration:none;color:#fafafa;border-width:0;border-style:solid;border-radius:0;background-color:#323a45;box-shadow:0 2px 5px 0 rgba(0,0,0,.2);-webkit-appearance:none;-moz-appearance:none}#platformCustomerModal button:hover,.platformComments button:hover{background-color:#24282c;box-shadow:0 4px 10px 0 rgba(0,0,0,.2)}#platformCustomerModal button:active,.platformComments button:active{transition-delay:0s;background-color:#008cba;box-shadow:0 8px 17px 0 rgba(0,0,0,.2)}#platformCustomerModal button.dismiss{position:absolute;top:0;right:0;width:70px;padding:.44444em;margin:0}#platformCustomerModal button.facebook{background:#344ea2}#platformCustomerModal button.google-plus{background:#dd4b39}#platformCustomerModal button.twitter{background:#00a7f6}#platformCustomerModal button.linkedin{background:#1c6eb6}#platformCustomerModal button.pinterest{background:#cc2127}#platformCustomerModal button.social{max-width:250px}@media screen and (min-width:1200px){#platformCustomerModal button.social{float:left;width:calc(25% - .50506em);margin:.25253em .25253em .88889em}}#platformCustomerModal .name,#platformCustomerModal .text-center{text-align:center}#platformCustomerModal label,.platformComments label{position:relative;display:none;float:left;width:100%;padding:.88889em;font-size:.88889em}#platformCustomerModal input,.platformComments input,.platformComments textarea{height:initial;position:relative;display:block;float:left;width:100%;padding:.88889em;margin:.25253em 0;font-size:.88889em;transition:all .2s cubic-bezier(.4,0,.2,1);color:#636363;border:none;border-bottom:1px solid #24282c;border-radius:0;background:#fafafa;-webkit-appearance:none}.platformComments textarea{border:1px solid #24282c}@media screen and (min-width:1000px){#platformCustomerModal input{float:left;width:calc(50% - .50506em);margin:.25253em}#platformCustomerModal input[name=displayName]{width:100%}}#platformCustomerModal input:focus,.platformComments input:focus{border-bottom:1px solid #008cba;outline:0;background:#fff}.platformComments textarea:focus{border:1px solid #008cba;background:#fff}#platformCustomerModal button[type=submit],.platformComments button[type=submit]{display:inline-block;margin-top:.88889em}#platformCustomerModal .muted,.platformComments .muted{color:silver}#platformCustomerModal .error,.platformComments .error{margin:.25253em 0;color:#f04124;clear:both;text-align:center}.platformComments .attribution{float:left;width:100%;font-size:12px}.platformComments .date{float:right}.platformComments .comment-body{margin:0 0 .88889em;font-size:15px}.platformComments .left{float:left;width:50px;min-height:50px;padding-right:15px;padding-bottom:15px}.platformComments .right{float:right;width:calc(100% - 65px)}.platformComments .left img{width:50px;height:50px}.platformComments .report{margin-left:10px;cursor:pointer}.platformComments .report:hover{color:#ff9800}.platformComments .pc-right{float:right}.platformComments .title{margin:20px 0 4px;border:none}.platformComments .pc-stars{color:#efaf27}.platformComments .pcfa-star-o{color:silver}.platformComments .commentForm .pc-stars{margin:8px 0;float:left;cursor:pointer;font-size:2em}.platformComments .comment .comment{padding:15px 0 0;border-top:1px solid #EFEFEF}'+
                '</style>');
            $('head').append(style);
            $('body').append($('<div id="platformCustomerModal"></div>'));

            /**
             * Targeting support for container. Either supports null (modal) or a specific element ID passed via configuration.
             */
            if (null != ClientConfig.values.bindTarget) {
                var check = ClientConfig.values.bindTarget.replace('#','');
                if (null != document.getElementById(check)) {
                    target = check;
                } else {
                    Debugger.error('PlatformJS: Could not find bindTarget '+check+'. Falling back to modal.');
                }
            }

            React.render(
                React.createElement(CustomerContainer, null),
                document.getElementById(target)
            );
        }

        this.login = function() {
            EventDispatcher.trigger('SignIn.login');
        }

        this.register = function() {
            EventDispatcher.trigger('SignIn.register');
        }

        this.logout = function() {
            EventDispatcher.trigger('SignIn.logout');
            CustomerManager.logout();
        }

        function setButtonState()
        {
            if (CustomerManager.isLoggedIn()) {
                $(ClientConfig.values.targets.loginButton).hide();
                $(ClientConfig.values.targets.registerButton).hide();
                $(ClientConfig.values.targets.logoutButton).show();
            } else {
                $(ClientConfig.values.targets.loginButton).show();
                $(ClientConfig.values.targets.registerButton).show();
                $(ClientConfig.values.targets.logoutButton).hide();
            }
        }

        function bindButtonEvents()
        {
            $(document).on('click', ClientConfig.values.targets.loginButton, function(e) {
                e.preventDefault();
                PlatformComponents.SignIn.login();
            }.bind(this));

            $(document).on('click', ClientConfig.values.targets.registerButton, function(e) {
                e.preventDefault();
                PlatformComponents.SignIn.register();
            }.bind(this));

            $(document).on('click', ClientConfig.values.targets.logoutButton, function(e) {
                e.preventDefault();
                PlatformComponents.SignIn.logout();
            }.bind(this));
        }
    }

    function CommentComponent() {

        var Comment = React.createClass({displayName: "Comment",

            getInitialState: function() {
                return {
                    data: [],
                    display: true
                }
            },

            componentDidMount: function() {
                if (true === this.props.data.reported) {
                    this.setState({display: false});
                }
                EventDispatcher.subscribe('CustomerManager.customer.loaded', function() {
                    this.forceUpdate();
                }.bind(this));

                EventDispatcher.subscribe('CustomerManager.customer.unloaded', function() {
                    this.forceUpdate();
                }.bind(this));
            },

            reportComment: function() {
                Ajax.send('/comments/report/'+this.props.data.id).then(
                    function(response) {
                        this.props.data.reported = true;
                        this.setState({display: false});
                    }.bind(this)
                );
            },

            toggleBody: function() {
                this.setState({display: !this.state.display});
            },

            render: function() {
                var title, body;
                if (this.props.data.title) {
                    title = React.createElement("h4", {className: "title"}, this.props.data.title);
                }

                if (true === this.props.data.reported) {
                    if (true === this.state.display) {
                        body = React.createElement("div", {className: "reported", onClick: this.toggleBody, style: {cursor: 'pointer'}},
                            React.createElement("p", {className: "comment-body"}, this.props.data.body)
                        )
                    } else {
                        body = React.createElement("div", {className: "reported", onClick: this.toggleBody, style: {cursor: 'pointer'}},
                            React.createElement("p", {className: "comment-body muted"}, "This comment has been reported. Click to view.")
                        )
                    }
                } else {
                    body = React.createElement("p", {className: "comment-body"}, this.props.data.body);
                }

                var report;
                if (CustomerManager.isLoggedIn() && !this.props.data.reported) {
                    report = React.createElement("i", {className: "pcfa pcfa-flag report", title: "Report Post", onClick: this.reportComment})
                }

                var children;
                if (this.props.data.hasOwnProperty('children')) {
                    children = React.createElement(CommentList, {data: this.props.data.children});
                }

                var attribution,
                    customer,
                    picture = ServerConfig.values.comments.default_avatar,
                    modPicture = ServerConfig.values.comments.moderator_avatar,
                    displayName = 'Anonymous',
                    isModerator = this.props.data.hasOwnProperty('moderator')
                ;

                if (this.props.data.hasOwnProperty('moderator')) {
                    if (false === this.props.data.anonymize) {
                        displayName = this.props.data.moderator.fields.displayName || 'Moderator';
                        picture = this.props.data.moderator.fields.picture || modPicture;
                    } else {
                        displayName = 'Moderator';
                        picture = modPicture;
                    }
                    attribution = React.createElement("div", {className: "attribution muted"},
                        React.createElement("span", {className: "date"},
                            React.createElement("date", null, this.props.data.created)
                        ),
                        React.createElement("span", null, "Posted by ", displayName)
                    );
                } else if (this.props.data.hasOwnProperty('customer')) {
                    if (false === this.props.data.anonymize) {
                        displayName = this.props.data.customer.fields.displayName || 'Unknown';
                        picture = this.props.data.customer.fields.picture || picture;
                    }
                    attribution = React.createElement("div", {className: "attribution muted"},
                        React.createElement("span", {className: "date"},
                            React.createElement("date", null, this.props.data.created),
                            report
                        ),
                        React.createElement("span", null, "Posted by ", displayName)
                    );
                } else {
                    if (false === this.props.data.anonymize) {
                        displayName = this.props.data.displayName || 'Unknown';
                        picture = this.props.data.picture || picture;
                    }
                    attribution = React.createElement("div", {className: "attribution muted"},
                        React.createElement("span", {className: "date"},
                            React.createElement("date", null, this.props.data.created),
                            report
                        ),
                        React.createElement("span", null, "Posted by ", displayName)
                    );
                }

                var className = 'comment';
                if (isModerator) {
                    className = className + ' moderator';
                }

                return (
                    React.createElement("div", {className: className},
                        React.createElement(PostAvatar, {picture: picture}),
                        React.createElement("div", {className: "right"},
                            title,
                            attribution,
                            body
                        ),
                        React.createElement('div', {className:'children'}, children)
                    )
                );
            }
        });

        var PostAvatar = React.createClass({displayName: "PostAvatar",
            render: function() {
                return (
                    React.createElement("div", {className: "left"},
                        React.createElement("img", {className: "media-img", src: this.props.picture, alt: "Avatar"})
                    )
                );
            }
        });

        var CommentList = React.createClass({displayName: "CommentList",
            render: function() {
                var commentNodes = this.props.data.map(function(comment, index) {
                    index = comment.id;
                    return (
                        React.createElement(Comment, {data: comment, key: index})
                    );
                });
                if (!commentNodes.length) {
                    commentNodes = React.createElement('p', {className: 'text-muted'}, 'No comments have been added yet. Want to start the conversation?');
                }
                return (
                    React.createElement("div", {className: "comments"},
                        commentNodes
                    )
                );
            }
        });

        var CommentForm = React.createClass({displayName: "CommentForm",

            getInitialState: function() {
                return {
                    errorMessage: null,
                    data: []
                };
            },

            handleSubmit: function(e) {
                e.preventDefault();
                this.setState({errorMessage: null});

                var comment = {
                    body: React.findDOMNode(this.refs.body).value.trim(),
                    streamTitle: ClientConfig.values.streamTitle || document.title || 'No title',
                    streamUrl: ClientConfig.values.streamUrl || window.location.href
                };

                if (!comment.body) {
                    this.setState({errorMessage: 'You cannot submit an empty comment.'});
                    return;
                }

                EventDispatcher.trigger('Comments.post.submit');
                var endpoint = '/comments/' + ClientConfig.values.comments.identifier;
                Ajax.send(endpoint, 'POST', comment).then(
                    function (response) {
                        EventDispatcher.trigger('Comments.post.success', [comment]);
                        React.findDOMNode(this.refs.body).value = '';
                    }.bind(this),
                    function (jqXHR) {
                        var error = jqXHR.responseJSON.error || {};
                        EventDispatcher.trigger('Comments.post.error', [error]);
                        React.findDOMNode(this.refs.body).value = '';
                    }.bind(this)
                );
            },

            componentDidMount: function() {
                EventDispatcher.subscribe('CustomerManager.customer.loaded', function() {
                    this.forceUpdate();
                }.bind(this));

                EventDispatcher.subscribe('CustomerManager.customer.unloaded', function() {
                    this.forceUpdate();
                }.bind(this));
            },

            render: function() {
                var authBlock,
                    fields = React.createElement("div", null,
                        React.createElement("label", {htmlFor: "inputBody", className: ""}, "Comments"),
                        React.createElement("textarea", {className: "", id: "inputBody", rows: "3", required: "required", ref: "body", placeholder: 'Add your comment here'}
                        ),
                        React.createElement("p", {className: "error"}, this.state.errorMessage),
                        React.createElement("button", {type: "submit", className: ""}, "Submit")
                    )

                if (ServerConfig.values.comments.force_login === false) {
                    authBlock = React.createElement("div", null,
                        React.createElement("label", {htmlFor: "inputEmail", className: ""}, "Email Address"),
                        React.createElement("input", {className: "", id: "inputEmail", type: "email", placeholder: "Enter email address", required: "required", ref: "email"}),
                        React.createElement("span", {className: "help-block"}, "Required")
                    )
                } else {
                    if (!CustomerManager.isLoggedIn()) {
                        authBlock = React.createElement("p", {className: "muted"}, "This site requires you to ", React.createElement("a", {style: {cursor:"pointer"}, onClick: PlatformComponents.SignIn.login}, "login"), " or ", React.createElement("a", {style: {cursor:"pointer"}, onClick: PlatformComponents.SignIn.register}, "register"), " to post a comment.")
                        fields = React.createElement("div", null)
                    } else {
                        authBlock = React.createElement("p", {className: ""},
                            "Posting as ", CustomerManager.getCustomer().fields.displayName,
                                React.createElement("input", {type: "hidden", name: "customer", value: CustomerManager.getCustomer().id})
                            )
                    }
                }
                return (
                    React.createElement("form", {className: "commentForm disabled", onSubmit: this.handleSubmit},
                        authBlock,
                        fields
                    )
                );
            }
        });

        var CommentBox = React.createClass({displayName: "CommentBox",

            loadCommentsFromServer: function() {
                retrieveComments().then(
                    function(response) {
                        this.setState({data: response.data});
                    }.bind(this),
                    function(xhr, status, err) {
                        Debugger.error(this.props.url, status, err.toString());
                    }.bind(this)
                );
            },

            getInitialState: function() {
                return {data: []};
            },

            componentDidMount: function() {
                EventDispatcher.subscribe('Comments.post.success', function() {
                    this.loadCommentsFromServer();
                }.bind(this));
                this.loadCommentsFromServer();
                // setInterval(this.loadCommentsFromServer, this.props.pollInterval);
            },

            render: function() {
                EventDispatcher.trigger('Comments.render');
                return (
                    React.createElement("div", null,
                        React.createElement("hr", null),
                        React.createElement("div", {className: "comments-container"},
                            React.createElement("h3", null, "Voice your opinion!"),
                            React.createElement(CommentList, {data: this.state.data}),
                            React.createElement(CommentForm, {onCommentSubmit: this.handleCommentSubmit})
                        )
                    )
                );
            }
        });

        function retrieveComments() {
            return Ajax.send('/comments/' + ClientConfig.values.comments.identifier, 'GET');
        }

        function submitComment(comment) {

        }

        this.render = function() {
            var check = ClientConfig.values.comments.bindTarget.replace('#',''),
                identifier = $('#'+check).data('identifier');
            if (null == document.getElementById(check)) {
                Debugger.warn('CommentComponent: Could not find comments.bindTarget #`'+check+'`.');
                return;
            }
            if (!identifier) {
                Debugger.error('CommentComponent: No `identifier` data attribute found on `#'+check+'`!');
                return;
            }

            document.getElementById(check).classList.add('platformComments');

            ClientConfig.values.comments.identifier = identifier;

            React.render(
                React.createElement(CommentBox, null),
                document.getElementById(check)
            );
        }
    }

    function ReviewComponent() {

        var Stars = React.createClass({displayName: "Stars",
            getInitialState: function() {
                return {
                    count: 0,
                    locked: true
                };
            },

            handleClick: function(i) {
                if (false === this.props.locked) {
                    this.setState({count: i});
                    this.props.setRating(i);
                }
            },

            getRating: function() {
                if (false === this.props.locked) {
                    return this.state.count;
                }
                return this.props.count;
            },

            render: function() {
                var stars = [], className;
                for (var i = 1; i <= 5; i++) {
                    if (i <= this.getRating()) {
                        className = 'pcfa pcfa-star';
                    } else {
                        className = 'pcfa pcfa-star-o';
                    }
                    stars.push(React.createElement('i', {className: className, onClick: this.handleClick.bind(this, i)}));
                };

                return (
                    React.createElement("div", {className: 'pc-stars'}, stars)
                );
            }
        });

        var Review = React.createClass({displayName: "Review",

            getInitialState: function() {
                return {
                    data: [],
                    display: true
                }
            },

            componentDidMount: function() {
                if (true === this.props.data.reported) {
                    this.setState({display: false});
                }
                EventDispatcher.subscribe('CustomerManager.customer.loaded', function() {
                    this.forceUpdate();
                }.bind(this));

                EventDispatcher.subscribe('CustomerManager.customer.unloaded', function() {
                    this.forceUpdate();
                }.bind(this));
            },

            reportComment: function() {
                Ajax.send('/reviews/report/'+this.props.data.id).then(
                    function(response) {
                        this.props.data.reported = true;
                        this.setState({display: false});
                    }.bind(this)
                );
            },

            toggleBody: function() {
                this.setState({display: !this.state.display});
            },

            render: function() {
                var
                    title,
                    body,
                    rating = 0
                ;
                if (this.props.data.hasOwnProperty('rating') && 0 > this.props.data.rating <= 5) {
                    rating = this.props.data.rating;
                }
                title = React.createElement("h4", {className: "title"},
                    this.props.data.title,
                    React.createElement('span', {className: 'pc-right'},
                        React.createElement(Stars, {count: rating})
                    )
                );

                if (true === this.props.data.reported) {
                    if (true === this.state.display) {
                        body = React.createElement("div", {className: "reported", onClick: this.toggleBody, style: {cursor: 'pointer'}},
                            React.createElement("p", {className: "comment-body"}, this.props.data.body)
                        )
                    } else {
                        body = React.createElement("div", {className: "reported", onClick: this.toggleBody, style: {cursor: 'pointer'}},
                            React.createElement("p", {className: "comment-body muted"}, "This review has been reported. Click to view.")
                        )
                    }
                } else {
                    body = React.createElement("p", {className: "comment-body"}, this.props.data.body);
                }

                var report;
                if (CustomerManager.isLoggedIn() && !this.props.data.reported) {
                    report = React.createElement("i", {className: "pcfa pcfa-flag report", title: "Report Post", onClick: this.reportComment})
                }


                var attribution,
                    customer,
                    picture = ServerConfig.values.comments.default_avatar,
                    displayName = 'Anonymous'
                ;

                if (this.props.data.hasOwnProperty('customer')) {
                    if (false === this.props.data.anonymize) {
                        displayName = this.props.data.customer.fields.displayName || 'Unknown';
                        picture = this.props.data.customer.fields.picture || picture;
                    }
                    attribution = React.createElement("div", {className: "attribution muted"},
                        React.createElement("span", {className: "date"},
                            React.createElement("date", null, this.props.data.created),
                            report
                        ),
                        React.createElement("span", null, "Posted by ", displayName)
                    );
                } else {
                    if (false === this.props.data.anonymize) {
                        displayName = this.props.data.displayName || 'Unknown';
                        picture = this.props.data.picture || picture;
                    }
                    attribution = React.createElement("div", {className: "attribution muted"},
                        React.createElement("span", {className: "date"},
                            React.createElement("date", null, this.props.data.created),
                            report
                        ),
                        React.createElement("span", null, "Posted by ", displayName)
                    );
                }

                return (
                    React.createElement("div", {className: "comment"},
                        React.createElement(PostAvatar, {picture: picture}),
                        React.createElement("div", {className: "right"},
                            attribution,
                            title,
                            body
                        )
                    )
                );
            }
        });

        var PostAvatar = React.createClass({displayName: "PostAvatar",
            render: function() {
                return (
                    React.createElement("div", {className: "left"},
                        React.createElement("img", {className: "media-img", src: this.props.picture, alt: "Avatar"})
                    )
                );
            }
        });

        var ReviewList = React.createClass({displayName: "ReviewList",
            render: function() {
                var commentNodes = this.props.data.map(function(comment, index) {
                    return (
                        React.createElement(Review, {data: comment, key: index})
                    );
                });
                if (!commentNodes.length) {
                    commentNodes = React.createElement('p', {className: 'text-muted'}, 'No reviews have been added yet. Want to start the conversation?');
                }
                return (
                    React.createElement("div", {className: "comments"},
                        commentNodes
                    )
                );
            }
        });

        var ReviewForm = React.createClass({displayName: "ReviewForm",

            getInitialState: function() {
                return {
                    errorMessage: null,
                    data: {
                        rating: 0
                    }
                };
            },

            handleSubmit: function(e) {
                e.preventDefault();
                this.setState({errorMessage: null});

                var comment = {
                    body: React.findDOMNode(this.refs.body).value.trim(),
                    title: React.findDOMNode(this.refs.title).value.trim(),
                    rating: this.state.data.rating || 0,
                    streamTitle: ClientConfig.values.streamTitle || document.title || 'No title',
                    streamUrl: ClientConfig.values.streamUrl || window.location.href
                };

                if (!comment.body) {
                    this.setState({errorMessage: 'You cannot submit an empty review.'});
                    return;
                }

                if (!comment.title) {
                    this.setState({errorMessage: 'You cannot submit a review without a title.'});
                    return;
                }

                if (!comment.rating || 0 === comment.rating) {
                    this.setState({errorMessage: 'You cannot submit a review without a rating.'});
                    return;
                }

                EventDispatcher.trigger('Comments.post.submit');
                var endpoint = '/reviews/' + ClientConfig.values.reviewIdentifier;
                Ajax.send(endpoint, 'POST', comment).then(
                    function (response) {
                        EventDispatcher.trigger('Comments.post.success', [comment]);
                        React.findDOMNode(this.refs.body).value = '';
                    }.bind(this),
                    function (jqXHR) {
                        var error = jqXHR.responseJSON.error || {};
                        EventDispatcher.trigger('Comments.post.error', [error]);
                        React.findDOMNode(this.refs.body).value = '';
                    }.bind(this)
                );
            },

            componentDidMount: function() {
                EventDispatcher.subscribe('CustomerManager.customer.loaded', function() {
                    this.forceUpdate();
                }.bind(this));

                EventDispatcher.subscribe('CustomerManager.customer.unloaded', function() {
                    this.forceUpdate();
                }.bind(this));
            },

            setRating: function(rating) {
                var data = this.state.data;
                data.rating = rating;
                this.setState({data:data})
            },

            render: function() {
                var authBlock,
                    fields = React.createElement("div", null,
                        React.createElement("div", {className:'review-title'},
                            React.createElement("label", {htmlFor: "inputTitle", className: ""}, "Title"),
                            React.createElement("input", {type:'text', id: "inputTitle", placeholder: 'Review Title', required: "required", ref: "title"}),
                            React.createElement("label", {htmlFor: "inputRating", className: "rating-input"}, "Rating"),
                            React.createElement(Stars, {count:this.state.data.rating, locked:false, setRating:this.setRating})
                        ),
                        React.createElement("label", {htmlFor: "inputBody", className: ""}, "Comments"),
                        React.createElement("textarea", {className: "", id: "inputBody", rows: "3", required: "required", ref: "body", placeholder: 'Add your review here'}),
                        React.createElement("p", {className: "error"}, this.state.errorMessage),
                        React.createElement("button", {type: "submit", className: ""}, "Submit")
                    )

                if (ServerConfig.values.comments.force_login === false) {
                    authBlock = React.createElement("div", null,
                        React.createElement("label", {htmlFor: "inputEmail", className: ""}, "Email Address"),
                        React.createElement("input", {className: "", id: "inputEmail", type: "email", placeholder: "Enter email address", required: "required", ref: "email"}),
                        React.createElement("span", {className: "help-block"}, "Required")
                    )
                } else {
                    if (!CustomerManager.isLoggedIn()) {
                        authBlock = React.createElement("p", {className: "muted"}, "This site requires you to ", React.createElement("a", {style: {cursor:"pointer"}, onClick: PlatformComponents.SignIn.login}, "login"), " or ", React.createElement("a", {style: {cursor:"pointer"}, onClick: PlatformComponents.SignIn.register}, "register"), " to post a comment.")
                        fields = React.createElement("div", null)
                    } else {
                        authBlock = React.createElement("p", {className: ""},
                            "Posting as ", CustomerManager.getCustomer().fields.displayName,
                                React.createElement("input", {type: "hidden", name: "customer", value: CustomerManager.getCustomer().id})
                            )
                    }
                }
                return (
                    React.createElement("form", {className: "commentForm disabled", onSubmit: this.handleSubmit},
                        authBlock,
                        fields
                    )
                );
            }
        });

        var ReviewBox = React.createClass({displayName: "ReviewBox",

            loadCommentsFromServer: function() {
                retrieveComments().then(
                    function(response) {
                        this.setState({data: response.data});
                    }.bind(this),
                    function(xhr, status, err) {
                        Debugger.error(this.props.url, status, err.toString());
                    }.bind(this)
                );
            },

            getInitialState: function() {
                return {data: []};
            },

            componentDidMount: function() {
                EventDispatcher.subscribe('Comments.post.success', function() {
                    this.loadCommentsFromServer();
                }.bind(this));
                this.loadCommentsFromServer();
                // setInterval(this.loadCommentsFromServer, this.props.pollInterval);
            },

            render: function() {
                EventDispatcher.trigger('Comments.render');
                return (
                    React.createElement("div", null,
                        React.createElement("hr", null),
                        React.createElement("div", {className: "comments-container"},
                            React.createElement("h3", null, "Voice your opinion!"),
                            React.createElement(ReviewList, {data: this.state.data}),
                            React.createElement(ReviewForm, {onCommentSubmit: this.handleCommentSubmit})
                        )
                    )
                );
            }
        });

        function retrieveComments() {
            return Ajax.send('/reviews/' + ClientConfig.values.reviewIdentifier, 'GET');
        }

        function submitComment(comment) {

        }

        this.render = function() {
            var check = ClientConfig.values.targets.reviewContainer.replace('#',''),
                identifier = $('#'+check).data('identifier');
            if (null == document.getElementById(check)) {
                Debugger.warn('CommentComponent: Could not find reviews.bindTarget #`'+check+'`.');
                return;
            }
            if (!identifier) {
                Debugger.error('CommentComponent: No `identifier` data attribute found on `#'+check+'`!');
                return;
            }

            document.getElementById(check).classList.add('platformComments');

            ClientConfig.values.reviewIdentifier = identifier;

            React.render(
                React.createElement(ReviewBox, null),
                document.getElementById(check)
            );
        }
    }

    function ComponentLoader()
    {
        EventDispatcher.subscribe('ready', function() {
            PlatformComponents.SignIn = new SignInComponent();
            if (true === ServerConfig.values.comments.enabled) {
                PlatformComponents.Comments = new CommentComponent();
                PlatformComponents.Reviews = new ReviewComponent();
            }
        });

        EventDispatcher.subscribe('CustomerManager.init', function() {
            PlatformComponents.SignIn.render();
            PlatformComponents.Comments.render();
            PlatformComponents.Reviews.render();
        });
    }

    function EventDispatcher()
    {
        this.trigger = function(key, parameters) {
            parameters = parameters || [];
            key = createKey(key);
            Debugger.info('Event dispatched', key, parameters);
            $(document).trigger(key, parameters);
        }

        this.subscribe = function(key, callback) {
            key = createKey(key);
            $(document).on(key, callback);
        }

        function createKey(key)
        {
            return 'PlatformComponents.' + key;
        }
    }

    function CustomerManager()
    {
        var customer = getDefaultCustomerObject();

        EventDispatcher.subscribe('CustomerManager.login.success', function() {
            EventDispatcher.trigger('CustomerManager.customer.loaded');
        });

        EventDispatcher.subscribe('ready', function() {
            this.init();
        }.bind(this));

        this.init = function() {
            this.checkAuth().then(function (response) {
                customer = response;
                EventDispatcher.trigger('CustomerManager.customer.loaded');
                EventDispatcher.trigger('CustomerManager.init');
            }, function () {
                Debugger.error('Unable to retrieve a customer.');
                EventDispatcher.trigger('CustomerManager.init');
            });
        }

        this.socialLogin = function(provider) {
            EventDispatcher.trigger('CustomerManager.login.submit');
            var headers = {
                'X-Auth-Service': 'Auth0',
            }
            auth0.signin(
                {
                    popup: true,
                    connection: provider
                },
                function (err, profile, id_token, access_token, state) {
                    if (null !== err) {
                        Debugger.warn('Social login failed', err);
                        EventDispatcher.trigger('CustomerManager.login.failure', 'There was an error authenticating with ' + provider);
                    } else {
                        headers['Authorization'] = 'Bearer ' + id_token;
                        return login(profile, headers);
                    }
                }
            );
        }

        this.databaseLogin = function(payload) {
            EventDispatcher.trigger('CustomerManager.login.submit');
            var headers = {
                'X-Auth-Service': 'Database',
            }
            return login(payload, headers);
        }

        this.isLoggedIn = function() {
            return null !== customer.id;
        }

        this.checkAuth = function() {

            var headers;
            if (Callbacks.has('checkAuth')) {
                headers = Callbacks.get('checkAuth')();
            }
            return Ajax.send('/check_auth', 'GET', undefined, headers);
        }

        this.getCustomer = function() {
            return customer;
        }

        this.logout = function() {
            if (this.isLoggedIn()) {

                var promise = Ajax.send('/logout', 'GET');
                    promise.then(function (response) {
                    // Success
                    customer = getDefaultCustomerObject();
                    EventDispatcher.trigger('CustomerManager.logout.success', [response]);
                    EventDispatcher.trigger('CustomerManager.customer.unloaded');
                },
                function(jqXHR) {
                    // Error
                    var error = jqXHR.responseJSON.error || {};
                    Debugger.warn('Unable to login customer', error);
                    EventDispatcher.trigger('CustomerManager.logout.failure', [error]);
                });

            } else {
                Debugger.warn('Tried to logout, already logged out.');
            }
        }

        this.databaseRegister = function(payload) {
            EventDispatcher.trigger('CustomerManager.register.submit');
            var headers = {
                'X-Auth-Service': 'Database',
            }
            var promise = Ajax.send('/signup', 'POST', payload, headers);
            promise.then(function (response) {
                // Success
                customer = response;
                EventDispatcher.trigger('CustomerManager.register.success', [response]);
                EventDispatcher.trigger('CustomerManager.login.success', [response]);
            },
            function(response) {
                var error = response.error || {};
                Debugger.warn('Unable to register customer', error);
                EventDispatcher.trigger('CustomerManager.register.failure', [error]);
            });
            return promise;

        }

        function login(payload, headers)
        {
            var promise = Ajax.send('/authenticate', 'POST', payload, headers);
            promise.then(function (response) {
                // Success
                customer = response;
                EventDispatcher.trigger('CustomerManager.login.success', [response, payload]);
            },
            function(object) {
                // Error
                var error = object.error || {};
                Debugger.warn('Unable to login customer', error);
                EventDispatcher.trigger('CustomerManager.login.failure', [error]);
            });
            return promise;
        }

        function getDefaultCustomerObject()
        {
            return {
                id: null,
                roles: [],
                fields: {}
            };
        }

    }

    function Ajax()
    {
        this.supports = function() {
            return ('object' === typeof XMLHttpRequest || 'function' === typeof XMLHttpRequest) && 'withCredentials' in new XMLHttpRequest();
        }

        function isJson(xhr) {
            return 'application/json' === xhr.getResponseHeader('content-type') && xhr.response.length;
        }

        function parse(xhr) {
            if (isJson(xhr)) {
                try {
                    return JSON.parse(xhr.response);
                } catch (e) {
                    Debugger.error('Unable to parse JSON response!', e);
                }
            }
            return xhr.response;
        }

        this.sendForm = function(url, method, payload, headers) {
            if (false === this.supports()) {
                Debugger.error('XHR unsupported!');
                return;
            }
            method = method || 'POST';
            headers = 'object' === typeof headers ? headers : {};
            return new RSVP.Promise(function(resolve, reject) {
                var xhr = new XMLHttpRequest();

                xhr.open(method, url, true);
                for (var i in headers) {
                    if (headers.hasOwnProperty(i)) {
                        xhr.setRequestHeader(i, headers[i]);
                    }
                }

                xhr.onreadystatechange = function() {
                    if (this.readyState === this.DONE) {
                        if (this.status >= 200 && this.status < 300) {
                            resolve(this);
                        } else {
                            reject(this);
                        }
                    }
                }

                if (payload) {
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                    xhr.send(payload);
                } else {
                    xhr.send();
                }
            });
        }

        this.send = function(endpoint, method, payload, headers) {
            if (false === this.supports()) {
                Debugger.error('XHR unsupported!');
                return;
            }
            method = method || 'POST';
            headers = 'object' === typeof headers ? headers : {};
            var url =  'http://' + ServerConfig.host + endpoint;

            return new RSVP.Promise(function(resolve, reject) {
                var xhr = new XMLHttpRequest();

                xhr.open(method, url, true);
                for (var i in headers) {
                    if (headers.hasOwnProperty(i)) {
                        xhr.setRequestHeader(i, headers[i]);
                    }
                }
                xhr.setRequestHeader('Content-Type', 'application/json');
                xhr.withCredentials = true;

                xhr.onreadystatechange = function() {
                    if (this.readyState === this.DONE) {
                        if (this.status >= 200 && this.status < 300) {
                            resolve(parse(this));
                        } else {
                            reject(parse(this));
                        }
                    }
                }

                if (payload) {
                    xhr.send(JSON.stringify(payload));
                } else {
                    xhr.send();
                }
            });
        }
    }

    function ServerConfig(hostname, serverConfig)
    {
        this.host = hostname;
        this.values = serverConfig;

        // Debugger.info('Server configuration loaded.', this.values);

        this.get = function(path) {

        }
    }

    function ClientConfig()
    {
        var defaults = {
            bindTarget: null,
            loginTitle: 'Log In',
            registerTitle: 'Sign Up',
            comments: {
                bindTarget: 'platformComments'
            },
            targets: {
                loginButton: '.platform-login',
                registerButton: '.platform-register',
                logoutButton: '.platform-logout',
                reviewContainer: 'platformReviews'
            },
            reviewIdentifier: null,
            callbacks: {
                checkAuth: undefined
            },
            streamTitle: null,
            streamUrl: null
        };

        var config = {};
        var scripts = document.getElementsByTagName("script");

        for (var i = scripts.length - 1; i >= 0; i--) {
            var src = scripts[i].src.toLowerCase();

            if (-1 < src.indexOf(ServerConfig.host)) {
                if ("" !== scripts[i].innerHTML.replace(/^\s+|\s+$/g, "")) {
                    try {
                        config = JSON.parse(scripts[i].innerHTML);
                        // Debugger.info('Client configuration loaded.', config);
                    } catch (e) {
                        Debugger.error('Configuration could not be parsed. Using defaults.', e);
                    }
                }
                break;
            }
        };

        $.extend(defaults, config);
        this.values = defaults;
    }

    function Debugger(enabled)
    {
        init();

        var enabled = Boolean(enabled) || false;

        this.enable = function() {
            enabled = true;
            return this;
        }

        this.disable = function() {
            enabled = false;
            return this;
        }

        this.log = function() {
            dispatch('log', arguments);
            return this;
        }

        this.info = function() {
            dispatch('info', arguments);
            return this;
        }

        this.warn = function() {
            dispatch('warn', arguments);
            return this;
        }

        this.error = function() {
            dispatch('error', arguments);
            return this;
        }

        /**
         *
         */
        function dispatch(method, passed)
        {
            if (true === enabled) {
                var args = ['COMPONENTS DEBUGGER:'];
                for (var i = 0; i < passed.length; i++)  {
                    var n = i + 1;
                    args[n] = passed[i];
                }
                console[method].apply(console, args);
            }
        }

        /**
         *
         */
        function init()
        {
            if (typeof console === 'undefined') {
                console = {};
            }
            var methods = ['log', 'info', 'warn', 'error'];
            for (var i = 0; i < methods.length; i++) {
                var method = methods[i];
                if (typeof console[method] === 'undefined') {
                    console[method] = function() {};
                }
            }
        }
    }

})(window.Radix = window.Radix || {});
