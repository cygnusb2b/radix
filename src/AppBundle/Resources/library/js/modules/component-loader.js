function ComponentLoaderModule()
{
    function actionMapping() {
        return {
            ResetPassword : {
                allowed      : [ 'token' ],
                required     : [ 'token' ]
            },
            VerifyEmail : {
                allowed      : [ 'token' ],
                required     : [ 'token' ]
            }
        };
    }

    /**
     * Gets the component propery mapping.
     * Determines which components and component properties are publically accessible.
     * If the component name is not listed here, it will not be available for access to the client's website.
     * Additionally, only the allowed properties will be passed from the data attributes to the component.
     */
    function propertyMapping() {
        return {
            EmailSubscriptions : {
                allowed      : [ 'title', 'className' ],
                required     : [  ],
                usesChildren : false
            },
            Inquiry : {
                allowed      : [ 'title', 'modelType', 'modelIdentifier', 'className', 'enableNotify', 'notifyEmail' ],
                required     : [ 'modelType', 'modelIdentifier' ],
                usesChildren : false
            },
            LinkLogout : {
                allowed      : [ 'tagName', 'wrappingTag', 'wrappingClass', 'className', 'label', 'prefix', 'suffix' ],
                required     : [  ],
                usesChildren : false
            },
            ModalLink : {
                allowed      : [ 'tagName', 'wrappingTag', 'wrappingClass', 'className', 'label', 'prefix', 'suffix', 'showLoggedIn', 'showLoggedOut' ],
                required     : [  ],
                usesChildren : true
            },
            ModalLinkLogin : {
                allowed      : [ 'tagName', 'wrappingTag', 'wrappingClass', 'className', 'label', 'prefix', 'suffix', 'title' ],
                required     : [  ],
                usesChildren : false
            },
            ModalLinkRegister : {
                allowed      : [ 'tagName', 'wrappingTag', 'wrappingClass', 'className', 'label', 'prefix', 'suffix', 'title' ],
                required     : [  ],
                usesChildren : false
            },
            ParseQueryString : {
                allowed      : [ 'className', 'query' ],
                required     : [  ],
                usesChildren : false
            },
        };
    }

    /**
     * Loads a React component (rendered as an element) for the provided jQuery object.
     */
    function loadComponentFor(jqObj) {
        var name  = jqObj.data('component');
        if (!name) {
            return;
        }

        var component = Radix.Components.get(name);
        if (!component) {
            return;
        }

        var props = parsePropsFrom(name, jqObj);
        if (false === props) {
            return;
        }

        Debugger.info('ComponentLoaderModule loadComponentFor()', name, props);
        return React.createElement(component, props);
    }

    /**
     * Parses the properties for a component and the corresponding jQuery object.
     */
    function parsePropsFrom(componentName, jqObj) {
        var dataAttrs = jqObj.data();
        var mapping   = propertyMapping()[componentName] || {};

        var props = cleanProps(componentName, dataAttrs, mapping, true);
        if (false === props) {
            return false;
        }

        if (mapping.usesChildren) {
            var children = jqObj.children('ins.radix');
            if (!children.length) {
                Debugger.error('ComponentLoaderModule', componentName, 'Requires that a child component element be present to render properly but none was found. Unable to load component.');
                return false;
            }
            var childComponent = loadComponentFor(children.eq(0));
            if (childComponent) {
                // Set the child component to the parent's properties.
                props['contents'] = childComponent;
            } else {
                Debugger.error('ComponentLoaderModule', componentName, 'Requires a child component class but none was found.');
            }
        }
        return props;
    }

    function cleanProps(componentName, props, mapping, scoped) {
        props   = props   || {};
        mapping = mapping || {};

        var propNames = mapping.allowed   || null;
        var required  = mapping.required  || [];

        if (!propNames) {
            // No public properties defined.
            Debugger.error('ComponentLoaderModule', componentName, 'No public properties defined for component.');
            return false;
        }

        var cleaned = {};
        for (var key in props) {
            if (props.hasOwnProperty(key)) {
                if (scoped && 0 !== key.indexOf('prop')) {
                    continue;
                }
                var propName = scoped ? Utils.lcFirst(key.replace('prop', '')) : key;
                if (-1 !== propNames.indexOf(propName)) {
                    cleaned[propName] = props[key];
                }
            }
        }

        for (var i = 0; i < required.length; i++) {
            var key = required[i];
            if (!cleaned.hasOwnProperty(key) || !cleaned[key]) {
                Debugger.error('ComponentLoaderModule', componentName, 'A required property was not found. Unable to load component. Expected:', key);
                return false;
            }
        }
        return cleaned;

    }

    function loadComponents() {
        $('ins.radix:not(ins.radix ins.radix)').each(function() {
            var component = loadComponentFor($(this));
            if (!component) {
                return;
            }
            React.render(component, $(this)[0]);
        });
    }

    function runAction() {
        var query = Utils.parseQueryString(window.location.search, true);
        if (!query.action) {
            // No action specified.
            return;
        }

        var mapping = actionMapping()[query.action] || null
        if (!mapping) {
            Debugger.error('ComponentLoaderModule', 'No public component available for action:', query.action);
            return;
        }

        var component = Radix.Components.get(query.action);
        if (!component) {
            Debugger.error('ComponentLoaderModule', 'No component found for action:', query.action);
            return;
        }

        var props = cleanProps(query.action, query, mapping, false);
        if (false === props) {
            return;
        }

        var element = React.createElement(component, props);
        var jqObj   = $('ins.radix-action');
        if (jqObj.length) {
            // Render to the action container.
            React.render(element, jqObj[0]);
        } else {
            // Use the modal.
            Radix.ModalModule.modal.setState({ contents: element });
            Radix.ModalModule.modal.show();
        }
    }

    /**
     * Finds all components on a page and renders them.
     */
    this.render = function() {
        // Load in-page components.
        loadComponents();
        // Handle query string action, if present.
        runAction();
    }
}
