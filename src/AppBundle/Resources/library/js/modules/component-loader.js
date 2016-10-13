function ComponentLoaderModule()
{
    /**
     * Gets the component propery mapping.
     * Determines which components and component properties are publically accessible.
     * If the component name is not listed here, it will not be available for access to the client's website.
     * Additionally, only the allowed properties will be passed from the data attributes to the component.
     */
    function propertyMapping() {
        return {
            ModalLink : {
                allowed      : [ 'tagName', 'className', 'label', 'showLoggedIn', 'showLoggedOut' ],
                required     : [  ],
                usesChildren : true
            },
            ModalLinkLogin : {
                allowed      : [ 'tagName', 'className', 'label', 'title' ],
                required     : [  ],
                usesChildren : false
            }
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
        Debugger.info('ComponentLoaderModule loadComponentFor()', name, props);
        return React.createElement(component, props);
    }

    /**
     * Parses the properties for a component and the corresponding jQuery object.
     */
    function parsePropsFrom(componentName, jqObj) {
        var dataAttrs = jqObj.data();
        var mapping   = propertyMapping()[componentName] || {};
        var propNames = mapping.allowed || null;
        if (!propNames) {
            // No public properties defined.
            Debugger.warn('ComponentLoaderModule', componentName, 'No public properties defined for component.');
            return {};
        }
        var props = {};
        for (var key in dataAttrs) {
            if (dataAttrs.hasOwnProperty(key)) {
                if (0 !== key.indexOf('prop')) {
                    continue;
                }
                var propName = Utils.lcFirst(key.replace('prop', ''));
                if (-1 !== propNames.indexOf(propName)) {
                    props[propName] = dataAttrs[key];
                }
            }
        }

        if (mapping.usesChildren) {
            var children = jqObj.children('ins.radix');
            if (!children.length) {
                Debugger.error('ComponentLoaderModule', componentName, 'Requires that a child component element be present to render properly but none was found.');
                return props;
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

    /**
     * Finds all components on a page and renders them.
     */
    this.render = function() {
        $('ins.radix:not(ins.radix ins.radix)').each(function() {
            var component = loadComponentFor($(this));
            if (!component) {
                return;
            }
            React.render(component, $(this)[0]);
        });
    }


}
