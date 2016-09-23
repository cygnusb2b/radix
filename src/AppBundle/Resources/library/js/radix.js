;
(function(Radix, undefined) {

    'use strict';
    var auth0;

    // Private properties
    var Debugger        = new Debugger();
    var Ajax            = new Ajax();
    // @todo Must load server config from the application, via /app/init
    // var ServerConfig    = new ServerConfig(hostname, serverConfig);
    var EventDispatcher = new EventDispatcher();
    var Callbacks       = new Callbacks();
    var Utils           = new Utils();

    var ClientConfig;
    var ComponentLoader;
    var CustomerManager;
    var LibraryLoader;

    // Radix.ajaxSend = function(url, method, payload, headers) {
    //     return Ajax.sendForm(url, method, payload, headers);
    // };

    Radix.on = function(key, callback) {
        EventDispatcher.subscribe(key, callback);
    };

    Radix.emit = function(key) {
        EventDispatcher.trigger(key);
    };

    Radix.init = function(config) {
        ClientConfig = new ClientConfig(config);
        if (true === ClientConfig.valid()) {
            Debugger.info('Configuration initialized and valid.');
            ComponentLoader = new ComponentLoader();
            CustomerManager = new CustomerManager();
            LibraryLoader   = new LibraryLoader();
        } else {
            Debugger.error('Client config is invalid. Ensure all require properties were set.');
        }
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
                '//checkout.stripe.com/checkout.js',
                '//cdn.auth0.com/w2/auth0-6.js'
            ];

        function loadLibraries() {
            for (var i = 0; i < libraries.length; i++) {
                Debugger.info('Loading library ' + libraries[i]);
                $.getScript(libraries[i]).then(function() {
                    if ('function' === typeof Auth0) {
                        // auth0 = new Auth0({
                        //     domain: ServerConfig.values.external_libraries.auth0.domain,
                        //     clientID: ServerConfig.values.external_libraries.auth0.client_id,
                        //     callbackOnLocationHash: true
                        // });
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

    function FormModule()
    {
        this.components = {

            FormSelectCountry: React.createClass({ displayName: 'FormSelectCountry',

                getDefaultProps: function() {
                    return {
                        selected: null
                    };
                },

                getInitialState: function() {
                    return {
                        loaded: false,
                        options: []
                    };
                },

                componentDidMount: function() {
                    Ajax.send('/app/util/country-options', 'GET').then(
                        function(response) {
                            this.setState({ loaded: true, options: response.data });
                        }.bind(this)
                    );
                },

                render: function() {
                    var props = {};
                    if (this.state.loaded) {
                        props = {
                            selected: this.props.selected,
                            options:  this.state.options
                        };
                    }
                    return (
                        React.createElement(Radix.FormModule.getComponent('FormSelect'), props)
                    )
                }

            }),

            FormSelect: React.createClass({ displayName: 'FormSelect',

                componentWillMount: function() {
                    this.insertPlaceholder(this.props);
                },

                componentWillReceiveProps: function(props) {
                    if (this.props.options.length !== props.options.length) {
                        // The options are going to change. Ensure the placeholder is added again.
                        this.insertPlaceholder(props);
                    }

                    // Handle the selected value.
                    var value = null;
                    if (props.selected) {
                        value = props.selected;
                    } else if (this.props.placeholder) {
                        value = this.props.placeholder;
                    }
                    this.setState({ value: value});
                },

                getDefaultProps: function() {
                    return {
                        className: 'form-element-field',
                        name: 'unknown',
                        disabled: false,
                        label: null,
                        placeholder: 'Please select...',
                        selected: null,
                        options: [],
                    };
                },

                getInitialState: function() {
                    return {
                        value: this.props.selected
                    }
                },

                getOptions: function() {
                    return this.props.options.map(function(option) {
                        option = Utils.isObject(option) ? option : {};
                        var optionProps = {
                            value: option.value || null,
                            label: option.label || null,
                        };
                        return React.createElement(Radix.FormModule.getComponent('FormSelectOption'), optionProps);
                    });
                },

                getSelectProps: function() {
                    return {
                        id        : 'form-element-field-' + this.props.name,
                        value     : this.state.value,
                        name      : this.props.name,
                        className : this.props.className,
                        onChange  : this.handleChange,
                        disabled  : this.props.disabled
                    };
                },

                handleChange: function(event) {
                    this.setState({ value: event.target.value })
                },

                insertPlaceholder: function(props) {
                    if (!this.props.placeholder) {
                        return;
                    }
                    props.options.unshift({
                        value: this.props.placeholder,
                        label: this.props.placeholder
                    });
                },

                render: function() {
                    return (
                        React.createElement('select', this.getSelectProps(), this.getOptions())
                    )
                }
            }),

            FormSelectOption: React.createClass({ displayName: 'FormSelectOption',

                getDefaultProps: function() {
                    return {
                        value: null,
                        label: null,
                    };
                },

                render: function() {
                    return (
                        React.createElement('option', {
                            value: this.props.value,
                            label: this.props.label
                        })
                    )
                }
            })
        };

        this.elements = {
            selectOption: function(props) {
                var defaults = {
                    value: '',
                    label: ''
                };

                $.extend(defaults, props);

                return React.createElement('option', { value: defaults.value }, defaults.label);
            },

            select: function(props) {
                var defaults = {
                    wrapperTagName: 'div',
                    name: 'unknown',
                    label: null,
                    value: null,
                    required: false,
                    disabled: false,
                    options: [],
                    onChange: null
                };

                $.extend(defaults, props);

                var label = defaults.label || Utils.titleize(defaults.name);
                var inputProps = {
                    id: 'form-element-field-' + defaults.name,
                    name: defaults.name,
                    ref: defaults.name,
                    className: 'form-element-field',
                    // placeholder: defaults.placeholder || label,
                    onChange: function(e) {
                        if ('function' === typeof defaults.onChange) {
                            defaults.onChange(e, this.props);
                        }
                        this.props.value = e.target.value;
                    }
                };

                if (defaults.value) inputProps.value = defaults.value;
                if (true === defaults.required) inputProps.required = 'required';
                if (true === defaults.disabled) inputProps.disabled = 'disabled';

                var options = defaults.options.map(function(option) {
                    return Radix.FormModule.get('selectOption', option);
                });

                return React.createElement(defaults.wrapperTagName, { className: 'form-element-wrapper '+defaults.name+'' },
                    React.createElement('select', inputProps, options),
                    React.createElement('label', { htmlFor: inputProps.id, className: 'form-element-label' }, label)
                );

            },

            textField: function(props) {
                var defaults = {
                    wrapperTagName: 'div',
                    name: 'unknown',
                    label: null,
                    placeholder: null,
                    value: null,
                    required: false,
                    autofocus: false,
                    autocomplete: true,
                    disabled: false,
                    type: 'text',
                    onKeyUp: null,
                    onBlur: null
                }
                $.extend(defaults, props);

                var label = defaults.label || Utils.titleize(defaults.name);
                var inputProps = {
                    id: 'form-element-field-' + defaults.name,
                    name: defaults.name,
                    type: defaults.type,
                    ref: defaults.name,
                    className: 'form-element-field',
                    placeholder: defaults.placeholder || label,
                    onChange: function(e) {
                        this.props.value = e.target.value;
                    }
                };

                if (defaults.value) inputProps.value = defaults.value;
                if (true === defaults.required) inputProps.required = 'required';
                if (true === defaults.autofocus) inputProps.autofocus = 'autofocus';
                if (true === defaults.disabled) inputProps.disabled = 'disabled';
                if (false === defaults.autocomplete) inputProps.autoComplete = 'off';
                if ('function' === typeof defaults.onKeyUp) inputProps.onKeyUp = defaults.onKeyUp;
                if ('function' === typeof defaults.onBlur) inputProps.onBlur = defaults.onBlur;
                return React.createElement(defaults.wrapperTagName, { className: 'form-element-wrapper '+defaults.name+'' },
                    React.createElement('input', inputProps),
                    React.createElement('label', { htmlFor: inputProps.id, className: 'form-element-label' }, label)
                );

            },
            textArea: function(props) {
                var defaults = {
                    wrapperTagName: 'div',
                    name: 'unknown',
                    label: null,
                    placeholder: null,
                    value: null,
                    rows: 3,
                    required: false,
                    autofocus: true,
                    disabled: false,
                    type: 'text',
                    onKeyUp: null,
                    onBlur: null
                }
                $.extend(defaults, props);

                var label = defaults.label || Utils.titleize(defaults.name);
                var inputProps = {
                    id: 'form-element-field-' + defaults.name,
                    name: defaults.name,
                    type: defaults.type,
                    ref: defaults.name,
                    rows: defaults.rows,
                    className: 'form-element-field',
                    placeholder: defaults.placeholder || label,
                    onChange: function(e) {
                        this.props.value = e.target.value;
                    }
                };

                if (defaults.value) inputProps.value = defaults.value;
                if (true === defaults.required) inputProps.required = 'required';
                if (true === defaults.autofocus) inputProps.autofocus = 'autofocus';
                if (true === defaults.disabled) inputProps.disabled = 'disabled';
                if ('function' === typeof defaults.onKeyUp) inputProps.onKeyUp = defaults.onKeyUp;
                if ('function' === typeof defaults.onBlur) inputProps.onBlur = defaults.onBlur;
                return React.createElement(defaults.wrapperTagName, { className: 'form-element-wrapper '+defaults.name+'' },
                    React.createElement('textarea', inputProps),
                    React.createElement('label', { htmlFor: inputProps.id, className: 'form-element-label' }, label)
                );

            }
        };

        this.has = function(key)
        {
            return null !== this.get(key);
        }

        this.getComponent = function(key)
        {
            if (this.components.hasOwnProperty(key)) {
                return this.components[key];
            }
            return null;
        }

        this.get = function(key, props)
        {
            props = props || null;
            if (this.elements.hasOwnProperty(key)) {
                return this.elements[key](props);
            }
            return null;
        }
    }

    function SubscriptionsComponent() {
        var bindingClass = 'platform-subscriptions';
        var defaults = {
            product: {
                id: null,
                name: 'Product Name',
                description: null,
                pricing: []
            },
            pricing: {
                name: 'Pricing',
                fullName: 'Pricing',
                description: null,
                cost: null
            },
            orders: []
        };

        var PricingItem = React.createClass({ displayName: 'SubPricingItem',
            getDefaultProps: function() {
                return {
                    containerId: 'platform-subscriptions',
                    product: defaults.product,
                    activePricing: defaults.pricing,
                    showPrice: true,
                }
            },
            componentDidMount: function() {

            },
            subscribe: function(e) {
                e.preventDefault();
                Radix.Subscriptions.renderSubscribe(this.props.containerId, this.props.product, this.props.activePricing);
            },
            render: function() {
                var name = this.props.activePricing.name;
                name = 'item-' + name.toLowerCase().replace(' ', '-');

                var price = (true === this.props.showPrice) ? React.createElement('p', { className: 'price' }, 'Price: $' + this.props.activePricing.cost) : '';
                return (
                    React.createElement('div', { className: 'pricing-item' },
                        React.createElement('h3', { className: 'name ' + name },
                            React.createElement('a', { className: 'link', onClick: this.subscribe, href: '#' }, this.props.activePricing.name)
                        ),
                        React.createElement('div', { className: 'description', dangerouslySetInnerHTML: {__html:this.props.activePricing.description} }),
                        price
                    )
                    );
            }
        });

        var PricingList = React.createClass({ displayName: 'SubPricingList',
            getDefaultProps: function() {
                return {
                    containerId: 'platform-subscriptions',
                    product: defaults.product,
                    pricingModels: []
                }
            },
            render: function() {
                var nodes = this.props.pricingModels.map(function(pricing, index) {
                    pricing.key = index;
                    return (
                        React.createElement(PricingItem, { containerId: this.props.containerId, product: this.props.product, activePricing: pricing } )
                    );
                }.bind(this));
                return (
                    React.createElement('div', {className: 'pricing-list'}, nodes)
                );
            }
        });

        var Product = React.createClass({ displayName: 'SubProduct',
            getDefaultProps: function() {
                return {
                    containerId: 'platform-subscriptions',
                    product: defaults.product,
                    showPricingList: true
                }
            },
            render: function() {
                var pricingList = (true === this.props.showPricingList) ? React.createElement(PricingList, { containerId: this.props.containerId, product: this.props.product, pricingModels: this.props.product.pricing }) : '';
                var activeSub = '';
                var customerOrders = CustomerManager.getCustomer().access.orders;
                if (0 < customerOrders.length) {
                    activeSub = React.createElement(ProductSubActive, { product: this.props.product, orders: customerOrders });
                }

                return (
                    React.createElement('div', {className: 'product'},
                        React.createElement('h2', {className: 'name' }, this.props.product.name),
                        React.createElement('p', { className: 'description', dangerouslySetInnerHTML: {__html:this.props.product.description} }),
                        activeSub,
                        pricingList
                    )
                );
            }
        });

        var ProductSubThankYou = React.createClass({ displayName: 'SubProductSubThankYou',
            getDefaultProps: function() {
                return {
                    product: defaults.product,
                    activePricing: defaults.pricing,
                    containerId: 'platform-subscriptions'
                };
            },
            render: function() {
                return (
                    React.createElement('div', { className: 'subscription-thank-you' },
                        React.createElement('h3', null, 'Thank you!'),
                        React.createElement('p', null, 'Thank you for purchasing the ' + this.props.activePricing.name + ' of ' + this.props.product.name + '! Your subscription is now active. ')
                    )
                );
            }
        });

        var ProductSubActive = React.createClass({ displayName: 'SubProductSubActive',
            getDefaultProps: function() {
                return {
                    product: defaults.product,
                    orders: defaults.orders
                };
            },
            render: function() {
                var text = [];
                var customer = CustomerManager.getCustomer();
                var groups = customer.groups;
                var activeOrder = CustomerManager.getActiveOrder(this.props.product.id);
                var hasAccess = false, hasGroupAccess = false;
                var name = customer.fields.hasOwnProperty('firstName') && null !== customer.fields.firstName ? customer.fields.firstName : customer.username;

                // Intro
                if (0 < groups.length) {
                    for (var i = 0; i < groups.length; i++) {
                        var groupOrder = CustomerManager.getActiveGroupOrder(this.props.product.id, groups[i].id);
                        if (null !== groupOrder) {
                            text.push(
                                React.createElement('p', null,
                                    'Your group, ' + groups[i].name + ', has a subscription through ',
                                    React.createElement('strong', null, groupOrder.end),
                                    '.'
                                )
                            );
                            text.push(React.createElement('p', { className: 'support' },
                                'To renew or extend a group subscription, please contact ',
                                React.createElement('a', { href: 'mailto:' + ServerConfig.values.notifications.subscriptions.email }, ServerConfig.values.notifications.subscriptions.name),
                                ' at ' + ServerConfig.values.notifications.subscriptions.phone + '.'
                            ));
                            hasGroupAccess = true;
                            break;
                        }
                    };
                }

                if (null !== activeOrder) {
                    hasAccess = true;
                    if (hasGroupAccess) {
                        text.push(React.createElement('hr'));
                        text.push(
                            React.createElement('p', null,
                                'You also have a personal subscription through ',
                                React.createElement('strong', null, activeOrder.end),
                                '.'
                            )
                        );
                    } else {
                        text.push(
                            React.createElement('p', null,
                                'You have a subscription through ',
                                React.createElement('strong', null, activeOrder.end),
                                '.'
                            )
                        );
                    }
                }

                var futureOrders = CustomerManager.getFutureOrders(this.props.product.id);
                if (0 < futureOrders.length) {
                    var orders = [];
                    for (var i = futureOrders.length - 1; i >= 0; i--) {
                        orders.push(
                            React.createElement('li', null,
                                React.createElement('strong', null, futureOrders[i].start),
                                ' through ',
                                React.createElement('strong', null, futureOrders[i].end)
                            )
                        );
                    };
                    text.push(
                        React.createElement('p', null,
                            'You have ',
                            React.createElement('strong', null, futureOrders.length),
                            ' future subscription(s): ',
                            React.createElement('ul', {className: 'order-list'}, orders)
                        )
                    );
                    text.push(
                        React.createElement('p', null, 'If you renew, your subscription will start on ',
                            React.createElement('strong', null, futureOrders[0].new_start), '.')
                    );
                    text.push(
                        React.createElement('p', null, 'To start your subscription on a different date, please contact ',
                            React.createElement('a', { href: 'mailto:' + ServerConfig.values.notifications.subscriptions.email }, ServerConfig.values.notifications.subscriptions.name),
                            ' at ' + ServerConfig.values.notifications.subscriptions.phone + '.'
                            )
                    );
                }

                return (
                    React.createElement('div', { className: 'subscription-active' },
                        React.createElement('h2', null, 'Hello, ' + name + '!'),
                        React.createElement('p', {className: 'active-text'}, text)
                    )
                );
            }
        });

        var ProductSubFormView = React.createClass({ displayName: 'SubProductSubFormView',
            paymentHandler: null,
            getDefaultProps: function() {
                return {
                    product: defaults.product,
                    activePricing: defaults.pricing,
                    containerId: 'platform-subscriptions'
                };
            },
            getInitialState: function() {
                return {
                    locked: false,
                    processing: false,
                    errorMessage: null
                };
            },
            componentDidMount: function() {
                if (true === CustomerManager.isLoggedIn()) {
                    this.initPaymentHandler();
                }

                EventDispatcher.subscribe('CustomerManager.customer.loaded', function() {
                    this.initPaymentHandler();
                    this.forceUpdate();
                }.bind(this));

                EventDispatcher.subscribe('CustomerManager.customer.unloaded', function() {
                    this.forceUpdate();
                }.bind(this));
            },
            handleSubmit: function(e) {
                e.preventDefault();

                this.setState({ locked: true });

                if (this.refs.email.props.value !== this.refs.confirmEmail.props.value) {
                    this.setState({errorMessage: 'Emails must match!', locked: false});
                    return false;
                }

                if (this.props.activePricing.cost > 0) {

                    // @todo Lock submit button
                    var
                        pricing = this.props.activePricing,
                        buttonLabel =  'Pay {{amount}} for ' + pricing.value + ' ' + pricing.interval
                    ;
                    if (pricing.value !== 1) buttonLabel = buttonLabel + 's';

                    this.paymentHandler.open({
                        name: pricing.name,
                        description: this.props.product.name,
                        amount: pricing.cost * 100,
                        allowRememberMe: false,
                        email: this.refs.email.props.value.trim(),
                        panelLabel: buttonLabel
                    });
                } else {
                    this.submitOrder();
                }
            },
            seeOther: function(e) {
                e.preventDefault();
                Radix.Subscriptions.renderDisplay(this.props.containerId);
            },
            render: function() {

                var contents, orders = '', instructions = 'Fill out the form below to complete your purchase.', title = 'Create new order';
                if (true === CustomerManager.isLoggedIn()) {
                    var customerOrders = CustomerManager.getCustomer().access.orders;
                    if (0 < customerOrders.length) {
                        orders = React.createElement(ProductSubActive, { product: this.props.product, orders: customerOrders });
                        if (true === CustomerManager.hasActiveSubscription(this.props.product.id)) {
                            title = 'Renew order';
                            instructions = 'Fill out the form below to renew or extend your order.';
                        }
                    }

                    instructions = React.createElement('div', null,
                        React.createElement('h2', {className: 'name'}, title),
                        React.createElement('p', {className: 'muted'}, instructions)
                    );
                    contents = this.getForm();

                } else {
                    instructions = React.createElement('p', null, "You must ", React.createElement("a", {style: {cursor:"pointer"}, onClick: Radix.SignIn.register}, "register"), ' to create a new subscription.', React.createElement('br'), 'Already have an account? ',  React.createElement("a", {style: {cursor:"pointer"}, onClick: Radix.SignIn.login}, "Login"), ' to subscribe.');
                }
                var name = this.props.activePricing.name;
                name = 'item-' + name.toLowerCase().replace(' ', '-');

                return (
                    React.createElement('div', { className: 'platform-element subscription-form' },
                        React.createElement('h2', { className: 'name' }, this.props.product.name),
                        React.createElement('p', { className: 'description' }, this.props.product.description),
                        orders,
                        React.createElement('div', { className: 'pricing-item' },
                            React.createElement('h3', { className: 'name ' + name }, this.props.activePricing.name),
                            React.createElement('div', { className: 'pricing-description', dangerouslySetInnerHTML: {__html: this.props.activePricing.description} }),
                            React.createElement('a', { href: '#', className: 'btn btn-default other-offers', onClick: this.seeOther }, 'See other packages/offers')
                        ),
                        instructions,
                        contents
                    )
                );
            },
            submitOrder: function(token)
            {
                if (false === CustomerManager.isLoggedIn()) {
                    Debugger.error('Cannnot submit order without an active customer.');
                    this.setState({ locked: false });
                    return false;
                }

                this.setState({ processing: true });

                var getValue = function(key) {
                    if (false === this.refs.hasOwnProperty(key)) {
                        return null;
                    }
                    if (false === this.refs[key].props.hasOwnProperty('value')) {
                        return null;
                    }
                    return this.refs[key].props.value.trim();
                }.bind(this);

                var customerId = CustomerManager.getCustomer().id;
                var productId = this.props.product.id;

                var payload = {
                    customer: {
                        firstName: getValue('firstName'),
                        lastName: getValue('lastName'),
                        email: getValue('email'),
                        companyName: getValue('companyName'),
                        title: getValue('title'),
                        phone: getValue('phone'),
                        fax: getValue('fax'),
                        address1: getValue('address1'),
                        address2: getValue('address2'),
                        city: getValue('city'),
                        region: getValue('region'),
                        postalCode: getValue('postalCode')
                    },
                    pricing: this.props.activePricing,
                    token: (Utils.isObject(token) && token.id) ? token.id : null
                };

                var promise = Ajax.send('/subscriptions/subscribe/' + productId + '/' + customerId , 'POST', payload);
                promise.then(function(response) {
                    if ('platform-access' === this.props.containerId) {
                        CustomerManager.reloadCustomer().then(function() {
                            Radix.Subscriptions.renderAccess(this.props.containerId);
                        }.bind(this));
                    } else {
                        CustomerManager.reloadCustomer();
                        Radix.Subscriptions.renderThankYou(this.props.containerId, this.props.product, this.props.activePricing);
                    }
                }.bind(this), function(error) {
                    Debugger.error('Subscribe failed.', error);
                    this.setState({ errorMessage: error, locked: false, processing: false });
                }.bind(this));
                return promise;
            },
            handlePaymentClose: function(e) {
                if (false === this.state.processing) {
                    this.setState({ locked: false });
                }

            },
            initPaymentHandler: function()
            {
                this.paymentHandler = StripeCheckout.configure({
                    key: ServerConfig.values.subscriptions.stripe.public_key,
                    locale: 'auto',
                    token: this.submitOrder,
                    closed: this.handlePaymentClose
                });
            },
            getValue: function(key)
            {
                if (true === this.refs.hasOwnProperty(key)) {
                    return this.refs[key].props.value;
                }
                return CustomerManager.getCustomer().fields[key];
            },
            getForm: function()
            {
                var buttonActionText = (this.props.activePricing.cost > 0) ? 'Pay and Subscribe' : 'Subscribe';
                var buttonProps = { type: 'submit' };
                var loading;
                if (this.state.locked) {
                    buttonProps.disabled = 'disabled';
                    loading = React.createElement('span', { className: 'pcfa pcfa-spinner pcfa-pulse' });
                }

                return React.createElement('form', { onSubmit: this.handleSubmit },
                    React.createElement('fieldset', { className: 'contact-info' },
                        React.createElement('legend', null, 'Contact Information'),
                        React.createElement("div", {className: ""},
                            Radix.FormModule.get('textField', { name: 'firstName', required: true, value: this.getValue('firstName') }),
                            Radix.FormModule.get('textField', { name: 'lastName', required: true, value: this.getValue('lastName') })
                        ),
                        React.createElement("div", {className: ""},
                            Radix.FormModule.get('textField', { type: 'email', name: 'email', label: 'Email Address', required: true, value: this.getValue('email') }),
                            Radix.FormModule.get('textField', { type: 'email', name: 'confirmEmail', label: 'Confirm Email Address', required: true, value: this.getValue('confirmEmail') })
                        ),
                        React.createElement("div", {className: ""},
                            Radix.FormModule.get('textField', { name: 'companyName', value: this.getValue('companyName') }),
                            Radix.FormModule.get('textField', { name: 'title', label: 'Job Title', value: this.getValue('title') })
                        ),
                        React.createElement("div", {className: ""},
                            Radix.FormModule.get('textField', { name: 'phone', value: this.getValue('phone') }),
                            Radix.FormModule.get('textField', { name: 'fax', value: this.getValue('fax') })
                        )
                    ),
                    React.createElement('fieldset', { className: 'billing-info' },
                        React.createElement('legend', null, 'Billing Information'),
                        Radix.FormModule.get('textField', { name: 'address1', label: 'Address', required: true, value: this.getValue('address1') }),
                        Radix.FormModule.get('textField', { name: 'address2', label: 'Address (Line 2)', value: this.getValue('address2') }),
                        React.createElement("div", {className: ""},
                            Radix.FormModule.get('textField', { name: 'city', required: true, value: this.getValue('city') }),
                            Radix.FormModule.get('textField', { name: 'region', required: true, label: 'State', value: this.getValue('region') }),
                            Radix.FormModule.get('textField', { name: 'postalCode', required: true, value: this.getValue('postalCode') })
                        )
                    ),
                    React.createElement('p', { className: 'terms-conditions' }, 'By submitting I agree to the ', React.createElement('a', { href: ServerConfig.values.subscriptions.component.terms_link }, 'Subscriber Agreement and Terms of Use')),
                    React.createElement('p', { className: 'support' }, 'If you have any questions about your subscription, or wish to purchase over the phone, please contact ', React.createElement('a', { href: 'mailto:' + ServerConfig.values.notifications.subscriptions.email }, ServerConfig.values.notifications.subscriptions.name), ' at ' + ServerConfig.values.notifications.subscriptions.phone + '.'),
                    React.createElement("p", {className: "error text-danger"}, this.state.errorMessage),
                    React.createElement("button", buttonProps, buttonActionText, ' ', loading)
                )
            }
        });

        var ProductList = React.createClass({ displayName: 'SubProductList',
            getDefaultProps: function() {
                return {
                    containerId: 'platform-subscriptions',
                    models: []
                }
            },
            render: function() {
                var nodes = this.props.models.map(function(product, index) {
                    product.key = index;
                    return (
                        React.createElement(Product, { containerId: this.props.containerId, product: product })
                    );
                }.bind(this));
                return (
                    React.createElement('div', {className: 'platform-element product-list'}, nodes)
                );
            }
        });

        var ProductContainer = React.createClass({ displayName: 'SubProductContainer',
            getDefaultProps: function() {
                return { containerId: 'platform-subscriptions' };
            },
            getInitialState: function() {
                return {data: []};
            },
            componentDidMount: function() {
                this.loadProductsFromServer();
            },
            render: function() {
                return (React.createElement(ProductList, { models: this.state.data, containerId: this.props.containerId }));
            },
            loadProductsFromServer: function() {
                Ajax.send('/subscriptions/products', 'GET').then(
                    function(response) {
                        this.setState({data: response.data});
                    }.bind(this),
                    function(xhr, status, err) {
                        Debugger.error('Unable to load subscription products', status, err.toString());
                    }.bind(this)
                );
            }
        });

        var ProductPricingList = React.createClass({ displayName: 'SubProductPricingList',
            render: function() {

            }
        });

        var AccessContainer = React.createClass({ displayName: 'SubAccessContainer',
            getDefaultProps: function() {
                return {
                    containerId: 'platform-access',
                    product: null
                };
            },
            componentDidMount: function() {
                EventDispatcher.subscribe('CustomerManager.customer.loaded', function() {
                    Radix.Subscriptions.renderAccess(this.props.containerId);
                }.bind(this));

                EventDispatcher.subscribe('CustomerManager.customer.unloaded', function() {
                    Radix.Subscriptions.renderAccess(this.props.containerId);
                }.bind(this));
            },
            render: function() {
                var contents;

                var customerType = (this.props.product) ? 'subscriber' : 'registered user';
                var regAction = (this.props.product) ? 'see subscription options' : 'view this content';

                if (false === CustomerManager.isLoggedIn()) {
                    contents = React.createElement('div', { className: 'login' },
                        React.createElement('h2', null, 'This content is exclusive to '+ customerType +'s.'),
                        React.createElement('p', null, 'Already a '+ customerType +'? ',
                            React.createElement('a', { style: { cursor: 'pointer' }, onClick: Radix.SignIn.login }, 'Login'),
                            ' to continue or ',
                            React.createElement('a', { style: { cursor: 'pointer' }, onClick: Radix.SignIn.register }, 'register'),
                            ' to ' + regAction + '.'
                        )
                    );
                } else {
                    contents = (!this.props.product) ? null : React.createElement('div', {className: 'subscribe'},
                        // React.createElement('h2', null, 'Become a member today!'),
                        React.createElement(Product, { containerId: this.props.containerId, product: this.props.product })
                    );
                }

                return (
                    React.createElement('div', { className: 'platform-element access-gate' },
                        contents
                    )
                );

            }
        });

        this.getContainerElement = function(id) {
            return document.getElementById(id);
        }

        this.render = function() {
            this.initContentAccess('platform-access');
            this.renderAccess('platform-access');
            this.renderDisplay('platform-subscriptions');
        }

        var accessControlledContent;
        var accessControlledProduct;

        this.initContentAccess = function(containerId) {
            var element = this.getContainerElement(containerId);
            if (!element) {
                return;
            }
            if (true === CustomerManager.isLoggedIn()) {
                EventDispatcher.subscribe('CustomerManager.customer.unloaded', function() {
                    Radix.Subscriptions.renderAccess(element.id);
                });
            }
            accessControlledContent = element.innerHTML;
        }

        this.renderAccess = function(containerId) {
            var element = this.getContainerElement(containerId);
            if (!element) {
                return;
            }

            var attributes = Utils.parseDataAttributes(element);
            var hasSubscriptionGate = true === attributes.hasOwnProperty('identifier') && 'string' === typeof attributes.identifier;
            var hasRegistrationGate = true === attributes.hasOwnProperty('registration') && 1 === parseInt(attributes.registration);

            if (false === hasSubscriptionGate && false === hasRegistrationGate) {
                // No gating restrictions
                element.innerHTML = accessControlledContent;
                return;
            }

            if (true === hasSubscriptionGate) {
                // Subscription gating restriction
                if (true === CustomerManager.isLoggedIn() && CustomerManager.hasSubscription(attributes.identifier)) {
                    // Customer is logged in and meets gating requirement
                    element.innerHTML = accessControlledContent;
                    return;
                }

                // Customer is not logged in, or doesn't meet the requirement
                if (!accessControlledProduct) {
                    Ajax.send('/subscriptions/product/' + attributes.identifier, 'GET').then(
                        function(response) {
                            element.innerHTML = '';
                            accessControlledProduct = response.data;
                            renderAccessElement(element, accessControlledProduct);
                        },
                        function(xhr, status, err) {
                            Debugger.error('Unable to load subscription product', xhr);
                        }
                    );
                } else {
                    renderAccessElement(element, accessControlledProduct);
                }
                return;
            }

            if (true === hasRegistrationGate) {
                if (true === CustomerManager.isLoggedIn()) {
                    // Customer is logged in and meets gating requirement
                    element.innerHTML = accessControlledContent;
                    return;
                }
                renderAccessElement(element, null);
                return;
            }
        }

        function renderAccessElement(element, product)
        {
            React.render(
                React.createElement(AccessContainer, { containerId: element.id, product: product }),
                element
            );
            document.body.scrollTop = element.offsetTop;
        }

        this.renderDisplay = function(containerId) {
            var element = this.getContainerElement(containerId);
            if (!element) {
                return;
            }
            React.render(
                React.createElement(ProductContainer, { containerId: element.id }),
                element
            );
            document.body.scrollTop = element.offsetTop;
        }

        this.renderSubscribe = function(containerId, product, activePricing) {
            var element = this.getContainerElement(containerId);
            if (!element) {
                return;
            }
            React.render(
                React.createElement(ProductSubFormView, { containerId: element.id, product: product, activePricing: activePricing }),
                element
            );
            document.body.scrollTop = element.offsetTop;
        }

        this.renderThankYou = function(containerId, product, activePricing) {
            var element = this.getContainerElement(containerId);
            if (!element) {
                return;
            }
            React.render(
                React.createElement(ProductSubThankYou, { containerId: element.id, product: product, activePricing: activePricing }),
                element
            );
            document.body.scrollTop = element.offsetTop;
        }
    }

    function InquiryComponent() {

        var InquiryForm = React.createClass({displayName: "InquiryForm",

            getDefaultProps: function() {
                return {
                    title: 'Request More Information',
                    model : {},
                    notify: {
                        enabled: false,
                        email: null
                    }
                }
            },

            getInitialState: function() {
                var customer = this._fillCustomer(CustomerManager.getCustomer());
                return {
                    customer: customer,
                    countryCode: customer.primaryAddress.countryCode || null,
                    countries: [],
                    errorMessage: null
                }
            },

            _fillCustomer: function(customer) {
                customer.primaryAddress = customer.primaryAddress || {};
                customer.primaryPhone   = customer.primaryPhone   || {};
                return customer;
            },

            countryChange: function(e) {
                this.setState({ countryCode: e.target.value });
            },

            componentDidMount: function() {

                EventDispatcher.subscribe('CustomerManager.customer.loaded', function() {
                    var customer = this._fillCustomer(CustomerManager.getCustomer());
                    this.setState({
                        customer: customer,
                        countryCode: customer.primaryAddress.countryCode || null
                    });
                }.bind(this));

                EventDispatcher.subscribe('CustomerManager.customer.unloaded', function() {
                    this.setState({
                        customer: this._fillCustomer(CustomerManager.getCustomer()),
                        countryCode: null
                    });
                }.bind(this));
            },

            render: function() {
                var form = this.getForm();
                var loginBlock;
                if (!this.state.customer._id) {
                    loginBlock = React.createElement("p", {className: "muted"}, "If you already have an account, you can ", React.createElement("a", {style: {cursor:"pointer"}, onClick: Radix.SignIn.login}, "login"), " to speed up this request.");
                }

                return (
                    React.createElement("div", null,
                        React.createElement("div", {className: "inquiry-container"},
                            React.createElement(Radix.FormModule.getComponent('FormSelectCountry'), { selected: this.state.customer.primaryAddress.countryCode }),
                            // Radix.FormModule.getComponent('FormSelect'),
                            // FormSelect,
                            React.createElement("h2", null, this.props.title),
                            loginBlock
                        ),
                        React.createElement("hr", null)
                        // form
                    )
                );
            },

            handleSubmit: function(e) {
                e.preventDefault();
            },

            getForm: function() {

                var customer     = this.state.customer;
                var disableEmail = (customer._id) ? true : false;

                var phoneLabel = customer.primaryPhone.phoneType + ' #';
                var phoneValue = customer.primaryPhone.number;

                // @todo Need to re-do form components as ACTUAL components, not as React.createElements that are returned.
                var postalCodeField;
                if ('USA' === this.state.countryCode || 'CAN' === this.state.countryCode) {
                    postalCodeField = Radix.FormModule.get('textField', { name: 'postalCode', label: 'Zip/Postal Code', autocomplete: false, value: customer.primaryAddress.postalCode });
                }

                return React.createElement("form", {className: "databaseForm", onSubmit: this.handleSubmit},
                    React.createElement('fieldset', { className: 'contact-info' },
                        React.createElement("div", {className: ""},
                            Radix.FormModule.get('textField', { name: 'givenName', label: 'First Name', required: true, autofocus: true, autocomplete: false, value: customer.givenName }),
                            Radix.FormModule.get('textField', { name: 'familyName', label: 'Last Name', required: true, autocomplete: false, value: customer.familyName })
                        ),
                        React.createElement("div", {className: ""},
                            Radix.FormModule.get('textField', { type: 'email', name: 'email', label: 'Email Address', disabled: disableEmail, required: true, autocomplete: false, value: this.state.customer.primaryEmail }),
                            Radix.FormModule.get('textField', { type: 'phone', name: 'phone', label: phoneLabel, required: false, autocomplete: false, value: phoneValue })
                        ),
                        React.createElement("div", {className: ""},
                            Radix.FormModule.get('textField', { name: 'companyName', label: 'Company Name', autocomplete: false, value: this.state.customer.companyName }),
                            Radix.FormModule.get('textField', { name: 'title', label: 'Job Title', autocomplete: false, value: this.state.customer.title })
                        ),
                        React.createElement("div", {className: ""},
                            Radix.FormModule.get('select', { name: 'countryCode', label: 'Country', required: true, value: this.state.countryCode, options: this.state.countries, onChange: this.countryChange }),
                            postalCodeField
                        )
                    ),
                    React.createElement("p", {className: "error text-danger"}, this.state.errorMessage),
                    React.createElement("div", {className: ""},
                        React.createElement("div", {className: ""},
                            React.createElement("button", {className: "", type: "submit"}, "Submit")
                        )
                    )
                );
            }

        });


        this.render = function() {
            var check = ClientConfig.values.targets.inquiryContainer.replace('#',''),
                model = {
                    identifier: $('#'+check).data('model-identifier'),
                    type:       $('#'+check).data('model-type')
                };

            if (null == document.getElementById(check)) {
                Debugger.warn('InquiryComponent: Could not find targets.inquiryContainer #`'+check+'`.');
                return;
            }
            if (!model.identifier) {
                Debugger.error('InquiryComponent: No `model-identifier` data attribute found on `#'+check+'`!');
                return;
            }
            if (!model.type) {
                Debugger.error('InquiryComponent: No `model-type` data attribute found on `#'+check+'`!');
                return;
            }

            document.getElementById(check).classList.add('platformInquiry');
            document.getElementById(check).classList.add('platform-element');

            var props = {
                customer: CustomerManager.getCustomer(),
                model  : model,
                notify : {
                    enabled : $('#'+check).data('enable-notify') || false,
                    email   : $('#'+check).data('notify-email') || null
                }
            }

            // ClientConfig.values.comments.identifier = identifier;
            React.render(
                React.createElement(InquiryForm, props),
                document.getElementById(check)
            );
        }
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
                    }
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
            },
            verifyConfirmPasswordField: function(e) {
                var confirmPassword = React.findDOMNode(this.refs.confirmPassword);

                if (!this.refs.confirmPassword.props.value || !this.refs.password.props.value) return;
                if (e.target.value !== this.refs.password.props.value) {
                    this.triggerMismatchError(confirmPassword, 'Password and Confirm Password must match!');
                } else {
                    this.clearMismatchError(confirmPassword);
                }
            },
            verifyPasswordField: function(e) {
                var confirmPassword = React.findDOMNode(this.refs.confirmPassword);

                if (!this.refs.password.props.value || !this.refs.confirmPassword.props.value) return;
                if (e.target.value !== this.refs.confirmPassword.props.value) {
                    this.triggerMismatchError(confirmPassword, 'Password and Confirm Password must match!');
                } else {
                    this.clearMismatchError(confirmPassword);
                }
            },
            verifyConfirmEmailField: function(e) {
                var confirmEmail = React.findDOMNode(this.refs.confirmEmail);

                if (!this.refs.confirmEmail.props.value || !this.refs.email.props.value) return;
                if (e.target.value !== this.refs.email.props.value) {
                    this.triggerMismatchError(confirmEmail, 'Email and Confirm Email must match!');
                } else {
                    this.clearMismatchError(confirmEmail);
                }
            },
            verifyEmailField: function(e) {
                var confirmEmail = React.findDOMNode(this.refs.confirmEmail);

                if (!this.refs.email.props.value || !this.refs.confirmEmail.props.value) return;
                if (e.target.value !== this.refs.confirmEmail.props.value) {
                    this.triggerMismatchError(confirmEmail, 'Email and Confirm Email must match!');
                } else {
                    this.clearMismatchError(confirmEmail);
                }
            },
            triggerMismatchError: function(target, message) {
                target.valid = false;
                // target.value = '';
                target.setCustomValidity(message);
            },
            clearMismatchError: function(target) {
                target.valid = true;
                target.setCustomValidity('');
                target.checkValidity();
            }
        });

        var RegisterContainer = React.createClass({displayName: "RegisterContainer",
            getInitialState: function() {
                return {
                    data: {},
                    locked: false
                }
            },
            componentDidMount: function() {
                EventDispatcher.subscribe('form.register.lock', function() {
                    this.setState({locked:true});
                    this.forceUpdate();
                }.bind(this));
                EventDispatcher.subscribe('form.register.unlock', function() {
                    this.setState({locked:false});
                    this.forceUpdate();
                }.bind(this));
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

                var locked;
                if (this.state.locked) {
                    locked = React.createElement('div', {className: 'form-lock'}, React.createElement('i', {className: 'pcfa pcfa-spinner pcfa-5x pcfa-pulse'}));
                }
                return (
                    React.createElement("div", {className: "register"},
                        React.createElement("p", {className: "error text-danger"}),
                        React.createElement("h2", {className: "name"}, "Sign Up"),
                        // providerNodes,
                        React.createElement(DatabaseRegister, {key: 0}),
                        locked
                    )
                );
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

            $(document).on('click', ClientConfig.values.targets.guidrSubmit, function(e) {
                e.preventDefault();
                Radix.GuidrSubmit.render();
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

    function CommentComponent() {

        var Comment = React.createClass({displayName: "Comment",

            getInitialState: function() {
                return {
                    data: [],
                }
            },

            componentDidMount: function() {
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
                        this.forceUpdate();
                    }.bind(this)
                );
            },

            render: function() {
                var title, body;
                if (this.props.data.title) {
                    title = React.createElement("h4", {className: "title"}, this.props.data.title);
                }

                if (true === this.props.data.reported) {
                    body = React.createElement("div", {className: "reported"},
                        React.createElement('p', {className: 'text-center strong'}, 'This comment has been reported.'),
                        React.createElement("p", {className: "comment-body muted"}, this.props.data.body)
                    )
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
                    // picture = ServerConfig.values.comments.default_avatar,
                    // modPicture = ServerConfig.values.comments.moderator_avatar,
                    picture,
                    modPicture,
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
                    body: React.findDOMNode(this.refs.inputBody).value.trim(),
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
                        React.findDOMNode(this.refs.inputBody).value = '';
                    }.bind(this),
                    function (jqXHR) {
                        var error = jqXHR.responseJSON.error || {};
                        EventDispatcher.trigger('Comments.post.error', [error]);
                        React.findDOMNode(this.refs.inputBody).value = '';
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
                        Radix.FormModule.get('textArea', { name: 'inputBody', label: 'Add your comment here', rows: "3", required: true }),
                        React.createElement("p", {className: "error"}, this.state.errorMessage),
                        React.createElement("button", {type: "submit", className: ""}, "Submit")
                    )

                // if (ServerConfig.values.comments.force_login === false) {
                //     authBlock = React.createElement("div", null,
                //             Radix.FormModule.get('textField', { name: 'inputEmail', label: 'Email Address', required: true }),
                //         React.createElement("span", {className: "help-block"}, "Required")
                //     )
                // } else {
                    if (!CustomerManager.isLoggedIn()) {
                        authBlock = React.createElement("p", {className: "muted"}, "This site requires you to ", React.createElement("a", {style: {cursor:"pointer"}, onClick: Radix.SignIn.login}, "login"), " or ", React.createElement("a", {style: {cursor:"pointer"}, onClick: Radix.SignIn.register}, "register"), " to post a comment.")
                        fields = React.createElement("div", null)
                    } else {
                        authBlock = React.createElement("p", {className: ""},
                            "Posting as ", CustomerManager.getCustomer().fields.displayName,
                                React.createElement("input", {type: "hidden", name: "customer", value: CustomerManager.getCustomer().id})
                            )
                    }
                // }
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
                        this.setState({data: response.data, total: response.total});
                    }.bind(this),
                    function(xhr, status, err) {
                        Debugger.error(this.props.url, status, err.toString());
                    }.bind(this)
                );
            },

            getInitialState: function() {
                return {data: [], total: 0};
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
                var check = ClientConfig.values.comments.detachedCount.bindTarget.replace('#','');
                if (null !== document.getElementById(check)) {
                    // update detached count if item is found
                    var identifier = document.getElementById(check).getAttribute('data-identifier');
                    if (identifier) {
                        document.getElementById(check).innerHTML = this.state.total;
                    } else {
                        Debugger.error('CommentComponent: No `identifier` data attribute found on `#'+check+'`!');
                    }
                } else {
                    Debugger.warn('CommentComponent: Could not find comments.detachedCount.bindTarget #`'+check+'`.');
                }

                return (
                    React.createElement("div", null,
                        React.createElement("hr", null),
                        React.createElement("div", {className: "comments-container"},
                            React.createElement("h3", null,
                                React.createElement("i", {className: "pcfa pcfa-comments-o"}),
                                ' '+ServerConfig.values.comments.call_to_action),
                            React.createElement(CommentForm, {onCommentSubmit: this.handleCommentSubmit}),
                            React.createElement(CommentList, {data: this.state.data})
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
            document.getElementById(check).classList.add('platform-element');

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
                    React.createElement(Stars, {count: rating}),
                    React.createElement('span', {className: 'pc-right'})
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
                    // picture = ServerConfig.values.comments.default_avatar,
                    picture,
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
                    body: React.findDOMNode(this.refs.inputBody).value.trim(),
                    title: React.findDOMNode(this.refs.inputTitle).value.trim(),
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
                            Radix.FormModule.get('textField', { name: 'inputTitle', label: 'Title', required: true })
                        ),
                        React.createElement("div", {className:'review-rating'},
                            React.createElement("label", {htmlFor: "inputRating", className: "rating-input"}, "Rating"),
                            React.createElement(Stars, {count:this.state.data.rating, locked:false, setRating:this.setRating})
                        ),
                        Radix.FormModule.get('textArea', { name: 'inputBody', label: 'Add your review here', rows: "3", required: true }),
                        React.createElement("p", {className: "error"}, this.state.errorMessage),
                        React.createElement("button", {type: "submit", className: ""}, "Submit")
                    )

                // if (ServerConfig.values.comments.force_login === false) {
                //     authBlock = React.createElement("div", null,
                //             Radix.FormModule.get('textField', { name: 'inputEmail', label: 'Email Address', required: true }),
                //         React.createElement("span", {className: "help-block"}, "Required")
                //     )
                // } else {
                    if (!CustomerManager.isLoggedIn()) {
                        authBlock = React.createElement("p", {className: "muted"}, "This site requires you to ", React.createElement("a", {style: {cursor:"pointer"}, onClick: Radix.SignIn.login}, "login"), " or ", React.createElement("a", {style: {cursor:"pointer"}, onClick: Radix.SignIn.register}, "register"), " to post a comment.")
                        fields = React.createElement("div", null)
                    } else {
                        authBlock = React.createElement("p", {className: ""},
                            "Posting as ", CustomerManager.getCustomer().fields.displayName,
                                React.createElement("input", {type: "hidden", name: "customer", value: CustomerManager.getCustomer().id})
                            )
                    }
                // }
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
                            React.createElement("h3", null,
                                React.createElement("i", {className: "pcfa pcfa-comments-o"}),
                                ' '+ServerConfig.values.comments.call_to_action),
                            React.createElement(ReviewForm, {onCommentSubmit: this.handleCommentSubmit}),
                            React.createElement(ReviewList, {data: this.state.data})
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

    function GuidrSubmitComponent() {

        var target = 'guidrSubmitModal';

        var SubmitForm = React.createClass({displayName: "SubmitForm",

            getInitialState: function() {
                return {
                    errorMessage: null,
                    data: []
                };
            },

            handleSubmit: function(e) {
                e.preventDefault();
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
                if (!CustomerManager.isLoggedIn()) {
                    return (
                        React.createElement("div", {className: "submit page wrap"},
                            React.createElement("button", {onClick: Radix.GuidrSubmit.disconnect, className: "dismiss"}, "Close"),
                            React.createElement("p", {className: "primary"}, "This site requires you to ", React.createElement("a", {style: {cursor:"pointer"}, onClick: Radix.SignIn.login}, "login"), " or ", React.createElement("a", {style: {cursor:"pointer"}, onClick: Radix.SignIn.register}, "register"), " to submit an event.")
                        )
                    );
                } else {
                    var customer = CustomerManager.getCustomer();
                            // for now this is hard coded to event
                            // @ToDo determine how to handle multiple model creation without replicating code
                            var src = '/guidr/submit/event?bpcs='+customer.bpcs;
                    return (
                        React.createElement("div", {className: "submit page wrap form"},
                            React.createElement("p", {className: "primary"}, "Thank you for taking the time to add an event. We reserve the right to edit event information."),
                            React.createElement("iframe", {src: src, id: "platform-iframe", style: {width:"100%", height: "100%"}}),
                            React.createElement("button", {onClick: Radix.GuidrSubmit.disconnect, className: "dismiss"}, "Close")
                        )
                    );
                }

            }
        });


        this.disconnect = function() {
            $('body').removeClass('show-modal');
            $('#guidrSubmitModal').remove();
        };

        this.render = function() {
            $('body').append($('<div id="guidrSubmitModal" class="platform-element" data-module="core" data-element="modal"></div>'));

            if (null != ClientConfig.values.bindTarget) {
                var check = ClientConfig.values.bindTarget.replace('#','');
                if (null != document.getElementById(check)) {
                    target = check;
                } else {
                    Debugger.error('PlatformJS: Could not find bindTarget '+check+'. Falling back to modal.');
                }
            }

            React.render(
                React.createElement(SubmitForm, null),
                document.getElementById(target)
            );

            $('body').addClass('show-modal');
            $('#guidrSubmitModal').show();
        }
    }

    function ComponentLoader()
    {
        EventDispatcher.subscribe('ready', function() {
            Radix.GuidrSubmit = new GuidrSubmitComponent();
            Radix.FormModule = new FormModule();
            Radix.SignIn = new SignInComponent();
            // if (true === ServerConfig.values.comments.enabled) {
                Radix.Comments = new CommentComponent();
                Radix.Reviews = new ReviewComponent();
            // }
            // if (true === ServerConfig.values.subscriptions.component.enabled) {
                Radix.Subscriptions = new SubscriptionsComponent();
            // }
            // if (true === ServerConfig.values.inquiry.component.enabled) {
                Radix.Inquiry = new InquiryComponent();
            // }
        });

        EventDispatcher.subscribe('CustomerManager.init', function() {
            var componentKeys = ['SignIn', 'Comments', 'Reviews', 'Subscriptions', 'Inquiry'];
            for (var i = 0; i < componentKeys.length; i++) {
                var key = componentKeys[i];
                if (true === Utils.isDefined(Radix[key])) {
                    Radix[key].render();
                }
            }
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
            return 'Radix.' + key;
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
                customer = response.data;
                EventDispatcher.trigger('CustomerManager.customer.loaded');
                EventDispatcher.trigger('CustomerManager.init');
            }, function () {
                Debugger.error('Unable to retrieve a customer.');
                EventDispatcher.trigger('CustomerManager.init');
            });
        }

        this.reloadCustomer = function() {
            var promise = this.checkAuth();
            promise.then(function (response) {
                customer = response.data;
            }, function() {
                Debugger.error('Unable to retrieve a customer.');
            });
            return promise;
        }

        this.hasSubscription = function(productId) {
            return -1 !== customer.access.subscriptions.indexOf(productId);
        }

        this.hasActiveSubscription = function(productId) {
            var orders = this.getOrders(productId);
            for (var i = 0; i < orders.length; i++) {
                if ('active' === orders[i].status) {
                    return true;
                }
            };
            return false;
        }

        // Return the latest active CUSTOMER order or null
        this.getActiveOrder = function(productId) {
            var orders = this.getOrders(productId);
            for (var i = 0; i < orders.length; i++) {
                if ('active' === orders[i].status) {
                    return orders[i];
                }
            };
            return null;
        }

        // Return the latest active GROUP order or null
        this.getActiveGroupOrder = function(productId, groupId) {
            var orders = this.getGroupOrders(productId, groupId);
            for (var i = 0; i < orders.length; i++) {
                if ('active' === orders[i].status) {
                    return orders[i];
                }
            };
            return null;
        }

        // return all future orders for the specified GROUP or empty array
        this.getFutureGroupOrders = function(productId, groupId) {
            var out = [];
            var orders = this.getGroupOrders(productId, groupId);
            for (var i = 0; i < orders.length; i++) {
                if ('pending' === orders[i].status) {
                    out.push(orders[i]);
                }
            };
            return out;
        }

        // return all future orders for this customer or empty array.
        this.getFutureOrders = function(productId) {
            var out = [];
            var orders = this.getOrders(productId);
            for (var i = 0; i < orders.length; i++) {
                if ('pending' === orders[i].status) {
                    out.push(orders[i]);
                }
            };
            return out;
        }

        this.getGroupOrders = function(productId, groupId) {
            var orders = [];
            for (var i = 0; i < customer.access.orders.length; i++) {
                if (productId === customer.access.orders[i].id && groupId === customer.access.orders[i].group) {
                    orders.push(customer.access.orders[i]);
                }
            };
            return orders;
        }

        this.getOrders = function(productId) {
            var orders = [];
            for (var i = 0; i < customer.access.orders.length; i++) {
                if (productId === customer.access.orders[i].id && 'Customer' === customer.access.orders[i].type) {
                    orders.push(customer.access.orders[i]);
                }
            };
            return orders;
        }

        this.getOrder = function(productId) {
            if (this.hasSubscription(productId)) {
                for (var i = 0; i < customer.access.orders.length; i++) {
                    if (productId === customer.access.orders[i].id) {
                        return customer.access.orders[i];
                    }
                };
            }
            return false;
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
                        EventDispatcher.trigger('form.login.unlock');
                        EventDispatcher.trigger('form.register.unlock');
                    } else {
                        headers['Authorization'] = 'Bearer ' + id_token;
                        return login(profile, headers);
                    }
                }
            );
        }

        this.generateDatabaseReset = function(parameters) {
            EventDispatcher.trigger('CustomerManager.reset.generate');
            var promise = Ajax.send('/reset/generate', 'POST', parameters);
            promise.then(function(response) {
                // Debugger.info('Generated reset code:', parameters.code);
                EventDispatcher.trigger('form.reset.unlock');
                EventDispatcher.trigger('CustomerManager.reset.generate.success');
            }, function(error) {
                Debugger.warn('Generate failed', error);
                EventDispatcher.trigger('form.reset.unlock');
                EventDispatcher.trigger('CustomerManager.reset.generate.failure');
            });
            return promise;
        }

        this.databaseResetCheck = function(parameters) {
            EventDispatcher.trigger('CustomerManager.reset.check');
            var promise = Ajax.send('/reset/check', 'POST', parameters);
            promise.then(function(response) {
                Debugger.info('Check passed');
                EventDispatcher.trigger('form.reset.unlock');
                EventDispatcher.trigger('CustomerManager.reset.check.success');
            }, function(error) {
                Debugger.warn('Check failed', error);
                EventDispatcher.trigger('form.reset.unlock');
                EventDispatcher.trigger('CustomerManager.reset.check.failure', [error]);
            });
            return promise;
        }

        this.databaseReset = function(parameters) {
            EventDispatcher.trigger('CustomerManager.reset.submit');
            var promise = Ajax.send('/reset', 'POST', parameters);
            promise.then(function(response) {
                Debugger.info('Reset code matched, proceed to reset pw screen.');
                EventDispatcher.trigger('form.reset.unlock');
                EventDispatcher.trigger('CustomerManager.reset.success');
            }, function(error) {
                Debugger.error('Reset failed!', error);
                EventDispatcher.trigger('form.reset.unlock');
                EventDispatcher.trigger('CustomerManager.reset.failure', [error]);
            });
            return promise;
        }

        this.databaseLogin = function(payload) {
            EventDispatcher.trigger('CustomerManager.login.submit');
            return login(payload);
        }

        this.isLoggedIn = function() {
            return 'undefined' !== typeof customer._id;
        }

        this.checkAuth = function() {

            var headers;
            if (Callbacks.has('checkAuth')) {
                headers = Callbacks.get('checkAuth')();
            }
            return Ajax.send('/app/auth', 'GET', undefined, headers);
        }

        this.getCustomer = function() {
            return customer;
        }

        this.logout = function() {
            if (this.isLoggedIn()) {

                var promise = Ajax.send('/app/auth/destroy', 'GET');
                    promise.then(function (response) {
                    // Success
                    customer = getDefaultCustomerObject();
                    EventDispatcher.trigger('CustomerManager.logout.success', [response]);
                    EventDispatcher.trigger('CustomerManager.customer.unloaded');
                },
                function(jqXHR) {
                    // Error
                    var errors  = jqXHR.errors|| [{}];
                    var error   = errors[0];
                    var message = error.detail || 'An unknown error occured.';

                    Debugger.warn('Unable to logout customer', errors);
                    EventDispatcher.trigger('CustomerManager.logout.failure', [message]);
                });

            } else {
                Debugger.warn('Tried to logout, already logged out.');
            }
        }

        this.databaseRegister = function(payload) {
            EventDispatcher.trigger('CustomerManager.register.submit');
            var promise = Ajax.send('/app/auth', 'POST', { data: payload });
            promise.then(function (response) {
                // Success
                customer = response.data;
                EventDispatcher.trigger('CustomerManager.register.success', [response]);
                EventDispatcher.trigger('CustomerManager.login.success', [response]);
            },
            function(jqXHR) {
                var errors  = jqXHR.errors|| [{}];
                var error   = errors[0];
                var message = error.detail || 'An unknown error occured.';

                Debugger.warn('Unable to register customer', errors);
                EventDispatcher.trigger('CustomerManager.register.failure', [message]);
            });
            return promise;

        }

        function login(payload, headers)
        {
            var promise = Ajax.send('/app/auth', 'PATCH', payload, headers);
            promise.then(function (response) {
                // Success
                customer = response.data;
                EventDispatcher.trigger('form.login.unlock');
                EventDispatcher.trigger('CustomerManager.login.success', [response, payload]);
            },
            function(jqXHR) {
                // Error
                var errors  = jqXHR.errors|| [{}];
                var error   = errors[0];
                var message = error.detail || 'An unknown error occured.';
                Debugger.warn('Unable to login customer', errors);
                EventDispatcher.trigger('form.login.unlock');
                EventDispatcher.trigger('CustomerManager.login.failure', [message]);
            });
            return promise;
        }

        function getDefaultCustomerObject()
        {
            return {};
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
            var url =  'http://' + ClientConfig.values.host + endpoint;

            return new RSVP.Promise(function(resolve, reject) {
                var xhr = new XMLHttpRequest();

                headers['Content-Type']  = 'application/json';
                headers['X-Radix-AppId'] = ClientConfig.values.appId;

                xhr.open(method, url, true);
                for (var i in headers) {
                    if (headers.hasOwnProperty(i)) {
                        xhr.setRequestHeader(i, headers[i]);
                    }
                }

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
                    Debugger.info('Sending XHR request', method, url, headers, payload);
                    xhr.send(JSON.stringify(payload));
                } else {
                    Debugger.info('Sending XHR request', method, url, headers);
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

    function ClientConfig(config)
    {
        config = 'object' === typeof config ? config : {};

        var defaults = {
            debug: false,
            host: null,
            appId: null,

            bindTarget: null,
            loginTitle: 'Log In',
            registerTitle: 'Sign Up',
            comments: {
                bindTarget: 'platformComments',
                detachedCount: {
                    bindTarget: 'platformCommentsCount'
                }
            },
            targets: {
                loginButton: '.platform-login',
                registerButton: '.platform-register',
                logoutButton: '.platform-logout',
                reviewContainer: 'platformReviews',
                inquiryContainer: 'platformInquiry',
                guidrSubmit: '.guidr-submit'
            },
            reviewIdentifier: null,
            callbacks: {
                checkAuth: undefined
            },
            streamTitle: null,
            streamUrl: null
        };

        $.extend(defaults, config);
        this.values = defaults;

        if (config.debug) {
            Radix.setDebug(this.values.debug);
        }
        Debugger.info('Config', this.values);

        this.valid = function() {
            var required = ['host', 'appId'];
            for (var i = 0; i < required.length; i++) {
                var key = required[i];
                if (!defaults[key]) {
                    return false;
                }
            }
            return true;
        }
    }

    function Utils()
    {
        this.isDefined = function(value)
        {
            return 'undefined' !== typeof value;
        }

        this.isObject = function(value)
        {
            return 'object' === typeof value;
        }

        this.show = function() {
            container.style.display = 'block';
        }

        this.hide = function() {
            container.style.display = 'none';
        }

        this.showElement = function(element, display)
        {
            display = display || getDefaultDisplay(element.tagName.toLowerCase());
            element.style.display = display;
        }

        this.hideElement = function(element)
        {
            element.style.display = 'none';
        }

        this.titleize = function(str) {
            var out = str.replace(/^\s*/, "");
            out = out.replace(/^[a-z]|[^\s][A-Z]/g, function(str, offset) {
                if (offset == 0) {
                    return(str.toUpperCase());
                } else {
                    return(str.substr(0,1) + " " + str.substr(1).toUpperCase());
                }
            });
            return(out);
        }

        this.parseDataAttributes = function(element)
        {
            var formatValue = function(value)
            {
                if (0 === value.length || "null" === value) {
                    return null;
                }
                return value;
            }

            var attributes = {};
            if ('object' === typeof element.dataset) {
                for (var prop in element.dataset) {
                    if (element.dataset.hasOwnProperty(prop)) {
                        attributes[prop] = formatValue(element.dataset[prop]);
                    }
                }
                return attributes;
            }

            for (var i = 0; i < element.attributes.length; i++) {
                var
                    attribute = element.attributes[i],
                    search = 'data-'
                ;
                if (0 === attribute.name.indexOf(search)) {
                    var prop = attribute.name.replace(search, '');
                    attributes[prop] = formatValue(attribute.value);
                }
            };
            return attributes;
        }

        function getDefaultDisplay(tagName)
        {
            var
                display,
                t = document.createElement(tagName),
                gcs = "getComputedStyle" in window
            ;
            document.body.appendChild(t);
            display = (gcs ? window.getComputedStyle(t, '') : t.currentStyle).display;
            document.body.removeChild(t);
            return display;
        }
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
