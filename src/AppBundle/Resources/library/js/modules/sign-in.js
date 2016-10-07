function SignInComponent()
{
    var target = 'platformCustomerModal';

    var RegisterContainer = React.createClass({displayName: "RegisterContainer",

        getDefaultProps: function() {
            return {
                title: 'Sign Up'
            };
        },

        getInitialState: function() {
            return {
                verify          : null
            }
        },

        componentDidMount: function() {
            var locker = this._formLock;
            var error  = this._error;

            EventDispatcher.subscribe('CustomerManager.register.success', function(e, response) {
                locker.unlock();

                var verify = {
                    emailAddress : response.data.email,
                    customerId   : response.data.customer
                };
                this.setState({ verify: verify });
            }.bind(this));

            EventDispatcher.subscribe('CustomerManager.register.failure', function (e, parameters, jqXHR) {
                locker.unlock();
                error.displayAjaxError(jqXHR);
            });
        },

        _formData: {},

        handleChange: function(event) {
            this._formData[event.target.name] = event.target.value;
        },

        handleSubmit: function(event) {
            event.preventDefault();

            var error  = this._error;
            error.clear();

            var data = this._formData;
            Debugger.info('RegisterContainer', 'handleSubmit', data);

            if (false === this._validateSubmit(data)) {
                return;
            }


            var locker = this._formLock;
            locker.lock();

            data['submission:referringHost'] = window.location.protocol + '//' + window.location.host;
            data['submission:referringHref'] = window.location.href;
            var payload = {
                data: data
            };

            CustomerManager.databaseRegister(payload);
        },

        _validateSubmit: function(data) {
            var error = this._error;
            if (!data['customer:password']) {
                error.display('The password field is required.');
                return false;
            }
            if (data['customer:password'].length < 4) {
                error.display('The password must be at least 4 characters long.');
                return false;
            }
            if (data['customer:password'].length > 72) {
                error.display('The password cannot be longer than 72 characters.');
                return false;
            }
            return true;
        },

        render: function() {
            // var providerNodes = ServerConfig.values.customer.auth.map(function(key, index) {
            //     if ('auth0' == key) {
            //         return (
            //             React.createElement(SocialLogin, {key: index})
            //         );
            //     }
            //     return (
            //         React.createElement(DatabaseRegister, {key: index})
            //     );
            // });

            var elements;
            if (!this.state.verify) {
                elements = React.createElement('div', null,
                    React.createElement(Radix.Forms.get('Register'), {
                        onSubmit    : this.handleSubmit,
                        onChange    : this.handleChange,
                        nextTemplate: this.state.nextTemplate
                    }),
                    React.createElement(Radix.Components.get('FormErrors'), { ref: this._setErrorDisplay }),
                    this._getSignInLink(),
                    React.createElement(Radix.Components.get('FormLock'),   { ref: this._setLock })
                );
            } else {
                elements = React.createElement('div', null,
                    React.createElement(Radix.Components.get('RegisterVerify'), this.state.verify)
                );
            }
            return (
                React.createElement('div', { className: 'register' },
                    React.createElement('h2', { className: 'name' }, this.props.title),
                    elements
                )
            );
        },

        _getSignInLink: function() {
            return React.createElement('p', {className: 'text-center muted'},
                'Already have an account? ',
                React.createElement('a', {href: 'javascript:void(0)', onClick: Radix.SignIn.login}, 'Sign in')
            );
        },

        _setErrorDisplay: function(ref) {
            this._error = ref;
        },

        _setLock: function(ref) {
            this._formLock = ref;
        }
    });

    var SocialButton = React.createClass({displayName: "SocialButton",
        getInitialState: function() {
            return {
                data: {
                    class: 'none',
                    label: 'None',
                }
            }
        },

        handleLogin: function(e) {
            e.preventDefault();
            EventDispatcher.trigger('form.login.lock');
            EventDispatcher.trigger('form.register.lock');
            CustomerManager.socialLogin(this.props.data.key);
        },
        render: function() {
            return (
                React.createElement("button", {className: "social sign-in " + this.props.data.class, onClick: this.handleLogin},
                    React.createElement('span', {className: 'icon'},
                        React.createElement("i", {className: "pcfa pcfa-"+this.props.data.class})
                    ),
                    React.createElement("span", null, this.props.data.label)
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
                username: React.findDOMNode(this.refs.username).value.trim(),
                password: React.findDOMNode(this.refs.password).value
            };

            if (!profile.username || !profile.password) {
                return;
            }

            EventDispatcher.trigger('form.login.lock');
            CustomerManager.databaseLogin({ data: profile });

            // React.findDOMNode(this.refs.email).value = ''; // Do not reset email.
            React.findDOMNode(this.refs.password).value = '';
        },

        getValue: function(key)
        {
            if (true === this.refs.hasOwnProperty(key)) {
                return this.refs[key].props.value;
            }
            return null;
        },

        render: function() {
            return (
                React.createElement("form", {className: "databaseForm", onSubmit: this.handleSubmit},
                    React.createElement("div", {className: ""},
                        // React.createElement("h4", {className: "text-center name"}, "OR"),
                        React.createElement("div", {className: ""},
                            Radix.FormModule.get('textField', { type: 'text', name: 'username', label: 'Username or Email', required: true, autofocus: "autofocus", value: this.getValue('username') }),
                            Radix.FormModule.get('textField', { type: 'password', name: 'password', label: 'Password', required: true, value: this.getValue('password') })
                        ),
                        React.createElement("div", {className: ""},
                            React.createElement("button", {className: "", type: "submit"}, "Sign In"),
                            React.createElement(
                                "p",
                                {className: "text-center muted"},
                                "Need an account? ",
                                React.createElement("a", {href: "javascript:void(0)", onClick: Radix.SignIn.register}, "Sign up!")
                                // React.createElement("br"),
                                // React.createElement("a", {href: "javascript:void(0)", onClick: Radix.SignIn.reset}, "Forgot your password?")
                            ),
                            React.createElement("hr"),
                            this._getSupportElement()
                        )
                    )
                )
            );
        },

        _getSupportElement: function() {
            var support = Application.settings.support || {};
            if (!support.email && !support.phone) {
                return;
            }

            var phoneElement;
            if (support.phone) {
                phoneElement = React.createElement('span', null, ' phone: ',
                    React.createElement('a', { href: 'tel:+1' + support.phone}, support.phone)
                );
            }
            var emailElement;
            if (support.email) {
                emailElement = React.createElement('span', null, ' email: ',
                    React.createElement('a', { href: 'mailto:' + support.email }, support.email)
                );
            }

            return React.createElement('p', {className: 'support text-center muted'},
                'Having problems logging in? Contact our customer support team...',
                React.createElement('br'),
                emailElement, phoneElement
            )
        }
    });

    var LoginContainer = React.createClass({displayName: "LoginContainer",
        getInitialState: function() {
            return {
                data: [],
                errorMessage: null,
                locked: false
            };
        },

        componentDidMount: function() {
            EventDispatcher.subscribe('CustomerManager.login.submit', function() {
                this.setState({errorMessage: null});
            }.bind(this));

            EventDispatcher.subscribe('CustomerManager.login.failure', function (e, parameters) {
                this.setState({errorMessage: parameters});
            }.bind(this));

            EventDispatcher.subscribe('form.login.lock', function() {
                this.setState({locked:true});
                this.forceUpdate();
            }.bind(this));
            EventDispatcher.subscribe('form.login.unlock', function() {
                this.setState({locked:false});
                this.forceUpdate();
            }.bind(this));
        },

        render: function() {
            // var providerNodes = ServerConfig.values.customer.auth.map(function(key, index) {
            //     if ('auth0' === key) {
            //         return (
            //             React.createElement(SocialLogin, {key: index})
            //         );
            //     }
            //     return (
            //         React.createElement(DatabaseLogin, {key: index})
            //     );
            // });
            var locked;
            if (this.state.locked) {
                locked = React.createElement('div', {className: 'form-lock'}, React.createElement('i', {className: 'pcfa pcfa-spinner pcfa-5x pcfa-pulse'}));
            }
            return (
                React.createElement("div", {className: "login-list"},
                    React.createElement("h2", {className: "name"}, "Log In"),
                    // providerNodes,
                    React.createElement(DatabaseLogin, {key: 0}),
                    React.createElement("p", {className: "error text-danger"}, this.state.errorMessage),
                    locked
                )
            );
        }
    });

    var LogoutContainer = React.createClass({displayName: "LogoutContainer",
        render: function() {
            return (
                React.createElement("div", {className: "login"},
                    React.createElement("h2", {className: "name"}, "You are currently logged in."),
                    React.createElement("p", null, React.createElement("a", {href: "javascript:void(0)", onClick: Radix.SignIn.logout}, "Logout"))
                )
            );
        }
    });

    var ResetContainer = React.createClass({displayName: "ResetContainer",
        getInitialState: function() {
            return {
                data: [],
                errorMessage: null,
                codeValid: false,
                locked: false
            };
        },

        handleSubmit: function(e) {
            e.preventDefault();
            EventDispatcher.trigger('form.reset.lock');
            this.setState({errorMessage: null});
            var profile = {
                email: React.findDOMNode(this.refs.inputEmail).value.trim(),
                code: React.findDOMNode(this.refs.inputCode).value.toUpperCase()
            };

            React.findDOMNode(this.refs.inputCode).value = profile.code;

            if (!profile.email || !profile.code) {
                this.setState({errorMessage: 'Email and code are required.'});
                return;
            }

            if (true === this.state.codeValid) {
                var password = React.findDOMNode(this.refs.password).value.trim(),
                    confirm = React.findDOMNode(this.refs.confirmPassword).value.trim();
                if (password !== confirm) {
                    this.setState({errorMessage: 'New passwords must match!'});
                    return;
                }
                profile.password = password;
                CustomerManager.databaseReset(profile);
            } else {
                CustomerManager.databaseResetCheck(profile);
            }
        },

        generate: function(e) {
            e.preventDefault();
            var email = React.findDOMNode(this.refs.inputEmail).value.trim();
            if (!email) {
                this.setState({errorMessage: 'Email is required!'});
                React.findDOMNode(this.refs.inputEmail).focus();
                return;
            }
            EventDispatcher.trigger('form.reset.lock');
            CustomerManager.generateDatabaseReset({email:email});
        },

        componentDidMount: function() {
            EventDispatcher.subscribe('CustomerManager.reset.submit', function() {
                this.setState({errorMessage: null});
            }.bind(this));
            EventDispatcher.subscribe('CustomerManager.reset.generate', function() {
                this.setState({errorMessage: null});
            }.bind(this));
            EventDispatcher.subscribe('CustomerManager.reset.check', function() {
                this.setState({errorMessage: null});
            }.bind(this));

            EventDispatcher.subscribe('CustomerManager.reset.generate.success', function (e, parameters) {
                this.setState({codeGenerated: true});
            }.bind(this));
            EventDispatcher.subscribe('CustomerManager.reset.generate.failure', function (e, parameters) {
                this.setState({errorMessage: parameters});
            }.bind(this));

            EventDispatcher.subscribe('CustomerManager.reset.check.success', function (e, parameters) {
                this.setState({codeValid: true});
            }.bind(this));
            EventDispatcher.subscribe('CustomerManager.reset.check.failure', function (e, parameters) {
                this.setState({codeValid: false, errorMessage: parameters});
            }.bind(this));

            EventDispatcher.subscribe('CustomerManager.reset.success', function (e, parameters) {
                alertify.success('Your password has been changed.');
            }.bind(this));
            EventDispatcher.subscribe('CustomerManager.reset.failure', function (e, parameters) {
                this.setState({errorMessage: parameters});
            }.bind(this));

            EventDispatcher.subscribe('form.reset.lock', function() {
                this.setState({locked:true});
                this.forceUpdate();
            }.bind(this));
            EventDispatcher.subscribe('form.reset.unlock', function() {
                this.setState({locked:false});
                this.forceUpdate();
            }.bind(this));
        },

        getValue: function(key)
        {
            if (true === this.refs.hasOwnProperty(key)) {
                return this.refs[key].props.value;
            }
            return null;
        },

        render: function() {
            if (true === this.state.codeValid) {
                var form = React.createElement("div", {className: ""},
                    React.createElement("div", {className: ""},
                        Radix.FormModule.get('textField', { type: 'email', name: 'inputEmail', label: 'Email Address', required: true, autofocus: "autofocus", value: this.getValue('inputEmail') })
                    ),
                    React.createElement("div", {className: ""},
                        Radix.FormModule.get('textField', { name: 'inputCode', label: 'Reset Code', required: true, value: this.getValue('inputCode') })
                    ),
                    React.createElement("div", {className: ""},
                        Radix.FormModule.get('textField', { type: 'password', name: 'password', label: 'Password', required: true, onBlur: this.verifyPasswordField, value: this.getValue('password') }),
                        Radix.FormModule.get('textField', { type: 'password', name: 'confirmPassword', label: 'Confirm Password', required: true, onBlur: this.verifyConfirmPasswordField, value: this.getValue('confirmPassword') })
                    )
                );
            } else {
                var form = React.createElement("div", {className: ""},
                    React.createElement("div", {className: ""},
                        Radix.FormModule.get('textField', { type: 'email', name: 'inputEmail', label: 'Email Address', required: true, autofocus: "autofocus", value: this.getValue('inputEmail') })
                    ),
                    React.createElement("div", {className: ""},
                        Radix.FormModule.get('textField', { name: 'inputCode', label: 'Reset Code', required: true, value: this.getValue('inputCode') })
                    )
                );
            }
            var locked;
            if (this.state.locked) {
                locked = React.createElement('div', {className: 'form-lock'}, React.createElement('i', {className: 'pcfa pcfa-spinner pcfa-5x pcfa-pulse'}));
            }

            return (
                React.createElement("form", {className: "resetForm", onSubmit: this.handleSubmit},
                    React.createElement("div", {className: "login-list"},
                        React.createElement("h2", {className: "name"}, "Reset Password"),
                        form,
                        React.createElement("p", {className:"text-muted text-center"}, "Don't have a reset code? ", React.createElement("a", {href: "javascript:void(0)", onClick: this.generate}, "Click here"), " to send a new one!"),
                        React.createElement("button", {className: "", type: "submit"}, "Reset your password"),
                        React.createElement("p", {className: "error text-danger"}, this.state.errorMessage),
                        React.createElement("hr"),
                        React.createElement('p', {className: 'support text-center muted'},
                            'Having problems logging in? Contact our customer support team: ',
                            React.createElement("br"),
                            React.createElement('a', { href: 'tel:+1' + ServerConfig.values.notifications.support.phone}, ServerConfig.values.notifications.support.phone),
                            ' or ',
                            React.createElement('a', { href: 'mailto:' + ServerConfig.values.notifications.support.email }, ServerConfig.values.notifications.support.email),
                            '.'
                        ),
                        locked
                    )
                )
            );
        }
    });

    var CustomerContainer = React.createClass({displayName: "CustomerContainer",

        getInitialState: function() {
            return {
                action: 'login'
            };
        },

        componentDidMount: function() {

            EventDispatcher.subscribe('SignIn.login', function() {
                this.setState({action: 'login'});
                this.show();
            }.bind(this));

            EventDispatcher.subscribe('SignIn.reset', function() {
                this.setState({action: 'reset'});
                this.show();
            }.bind(this));

            EventDispatcher.subscribe('SignIn.register', function(e) {
                if (true === e.isDefaultPrevented()) {
                    return;
                }
                this.setState({action: 'register'});
                this.show();
            }.bind(this));

            EventDispatcher.subscribe('CustomerManager.login.success', function() {
                this.hide();
            }.bind(this));

            EventDispatcher.subscribe('CustomerManager.reset.success', function() {
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
            // if (true == ServerConfig.values.customer.enabled) {
                var contents;
                switch (this.state.action) {
                    case 'register':
                        contents = React.createElement(RegisterContainer, null);
                        break;
                    case 'reset':
                        contents = React.createElement(ResetContainer, null);
                        break;
                    default:
                        if (false === CustomerManager.isLoggedIn()) {
                            contents = React.createElement(LoginContainer, null);
                        } else {
                            contents = React.createElement(LogoutContainer, null);
                        }
                };

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
            // } else {
            //     Debugger.error('PlatformJS: Customer component is not enabled.');
            // }
        }
    });

    this.render = function() {
        setButtonState();
        bindButtonEvents();

        EventDispatcher.subscribe('CustomerManager.customer.loaded', setButtonState);
        EventDispatcher.subscribe('CustomerManager.customer.unloaded', setButtonState);

        $('body').append($('<div id="platformCustomerModal" class="platform-element" data-module="core" data-element="modal"></div>'));

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

    this.reset = function() {
        EventDispatcher.trigger('SignIn.reset');
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
            Radix.SignIn.login();
        }.bind(this));

        $(document).on('click', ClientConfig.values.targets.registerButton, function(e) {
            e.preventDefault();
            Radix.SignIn.register();
        }.bind(this));

        $(document).on('click', ClientConfig.values.targets.logoutButton, function(e) {
            e.preventDefault();
            Radix.SignIn.logout();
        }.bind(this));
    }
}
