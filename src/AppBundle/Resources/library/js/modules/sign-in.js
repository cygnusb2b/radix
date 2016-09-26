function SignInComponent()
{
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
                // @todo Eventually the form fields themselves should be namespaced and linked to a model
                // Example app:customer-account:givenName or app:customer-account:emails[0][value]
                givenName: React.findDOMNode(this.refs.givenName).value.trim(),
                familyName: React.findDOMNode(this.refs.familyName).value.trim(),
                companyName: React.findDOMNode(this.refs.companyName).value.trim(),
                title: React.findDOMNode(this.refs.title).value.trim(),
                // displayName: React.findDOMNode(this.refs.displayName).value.trim(),

                emails: [
                    {
                        value: React.findDOMNode(this.refs.email).value.trim(),
                        // confirm: React.findDOMNode(this.refs.confirmEmail).value,
                        isPrimary: true
                    }
                ],

                credentials: {
                    password: {
                        value: React.findDOMNode(this.refs.password).value,
                    }
                },
                formData: this._formData
            };

            if (payload.credentials.password.value) {
                if (payload.credentials.password.value.length < 4) {
                    this.setState({errorMessage: 'Password must be at least 4 characters!'});
                    return false;
                } else if (payload.credentials.password.value.length > 4096) {
                    this.setState({errorMessage: 'Password must be less than 4096 characters!'});
                    return false;
                }
            }

            // if (payload.emails[0].value !== payload.emails[0].confirm) {
            //     this.setState({errorMessage: 'Emails must match!'});
            //     return false;
            // }

            CustomerManager.databaseRegister(payload);

            React.findDOMNode(this.refs.password).value = '';
        },

        componentDidMount: function() {
            EventDispatcher.subscribe('CustomerManager.register.submit', function() {
                this.setState({errorMessage: null});
            }.bind(this));

            EventDispatcher.subscribe('CustomerManager.register.failure', function (e, parameters) {
                this.setState({errorMessage: parameters});
            }.bind(this));
        },

        handleChange: function(event) {
            console.info('handleChange', event.target.name, event.target.value);
        },

        _formData: {},

        getValue: function(key)
        {
            if (true === this.refs.hasOwnProperty(key)) {
                return this.refs[key].props.value;
            }
            if (true === this._formData.hasOwnProperty(key)) {
                return this._formData.hasOwnProperty(key);
            }
            return null;
        },

        render: function() {
            return (
                React.createElement("form", {className: "databaseForm", onSubmit: this.handleSubmit},
                    React.createElement("div", {className: ""}
                        // React.createElement("h4", {className: "text-center name"}, "OR")
                    ),
                    React.createElement('fieldset', { className: 'contact-info' },
                        // React.createElement('legend', null, 'Contact Information'),
                        React.createElement("div", {className: ""},
                            Radix.FormModule.get('textField', { name: 'givenName', label: 'First Name', required: true, autofocus: true, autocomplete: false, value: this.getValue('givenName') }),
                            Radix.FormModule.get('textField', { name: 'familyName', label: 'Last Name', required: true, autocomplete: false, value: this.getValue('familyName') })
                        ),
                        React.createElement("div", {className: ""},
                            Radix.FormModule.get('textField', { type: 'email', name: 'email', label: 'Email Address', required: true, autocomplete: false, value: this.getValue('email') }),
                            Radix.FormModule.get('textField', { type: 'password', name: 'password', label: 'Password', required: true, autocomplete: false, value: this.getValue('password') })
                        ),
                        React.createElement("div", {className: ""},
                            Radix.FormModule.get('textField', { name: 'companyName', label: 'Company Name', autocomplete: false, value: this.getValue('companyName') }),
                            Radix.FormModule.get('textField', { name: 'title', label: 'Job Title', autocomplete: false, value: this.getValue('title') })
                        ),
                        React.createElement("div", {className: ""},
                            React.createElement(Radix.Components.get('CountryPostalCode'), { onChange: this.handleChange, postalCode: this.getValue('customer:primaryAddress.postalCode'), countryCode: this.getValue('customer:primaryAddress.countryCode') })
                        ),
                        // @todo In this situation, there isn't a customer, so there aren't any answers to extract -- a default customer object would fix this issue!
                        React.createElement('div', null,
                            React.createElement(Radix.Components.get('FormQuestion'), { onChange: this.handleChange, tagKeyOrId: 'business-code' }),
                            React.createElement(Radix.Components.get('FormQuestion'), { onChange: this.handleChange, tagKeyOrId: 'title-code' })
                        )
                    ),
                    React.createElement("p", {className: "error text-danger"}, this.state.errorMessage),
                    React.createElement("div", {className: ""},
                        React.createElement("div", {className: ""},
                            React.createElement("button", {className: "", type: "submit"}, "Sign Up"),
                            React.createElement("p", {className: "text-center muted"}, "Already have an account? ", React.createElement("a", {href: "javascript:void(0)", onClick: Radix.SignIn.login}, "Sign in"), " .")
                        )
                    )

                )
            );
        }
    });

    var RegisterContainer = React.createClass({displayName: "RegisterContainer",

        getDefaultProps: function() {
            return {
                title: 'Sign Up'
            };
        },

        getInitialState: function() {
            return {
                error: null
            };
        },

        componentDidMount: function() {
            var locker = this._formLock;
            var error  = this._error;

            EventDispatcher.subscribe('CustomerManager.register.success', function() {
                locker.unlock();
            });

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
            Debugger.info('RegisterContainer', 'handleSubmit', this._formData);

            var locker = this._formLock;

            locker.lock();

            var payload = {
                data: this._formData
            };

            CustomerManager.databaseRegister(payload);

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

            return (
                React.createElement('div', { className: 'register' },
                    React.createElement('h2', { className: 'name' }, this.props.title),
                    React.createElement(Radix.Forms.get('Register'), {
                        onSubmit    : this.handleSubmit,
                        onChange    : this.handleChange
                    }),
                    React.createElement(Radix.Components.get('FormErrors'), { ref: this._setErrorDisplay }),
                    this._getSignInLink(),
                    React.createElement(Radix.Components.get('FormLock'),   { ref: this._setLock })
                )
            );
        },

        _getSignInLink: function() {
            return React.createElement('p', {className: 'text-center muted'},
                'Already have an account? ',
                React.createElement('a', {href: 'javascript:void(0)', onClick: Radix.SignIn.login}, 'Sign in'),
                ' .'
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
                                React.createElement("a", {href: "javascript:void(0)", onClick: Radix.SignIn.register}, "Sign up!"),
                                React.createElement("br"),
                                React.createElement("a", {href: "javascript:void(0)", onClick: Radix.SignIn.reset}, "Forgot your password?")
                            ),
                            React.createElement("hr"),
                            React.createElement('p', {className: 'support text-center muted'},
                                'Having problems logging in? Contact our customer support team: ',
                                React.createElement("br"),
                                // React.createElement('a', { href: 'tel:+1' + ServerConfig.values.notifications.support.phone}, ServerConfig.values.notifications.support.phone),
                                ' or ',
                                // React.createElement('a', { href: 'mailto:' + ServerConfig.values.notifications.support.email }, ServerConfig.values.notifications.support.email),
                                '.'
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
