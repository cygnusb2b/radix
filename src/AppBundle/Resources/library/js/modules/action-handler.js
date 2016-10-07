function ActionHandlerModule()
{
    this.config = {
        target: '.radix-module-action-handler'
    }

    this.module = React.createClass({ displayName: 'ActionHandlerModule',

        getDefaultProps: function() {
            return {
                componentName: null,
            };
        },

        render: function() {
            var name = this.props.componentName;
            var element;
            if (Radix.Components.has(name)) {
                element = React.createElement(Radix.Components.get(name), this.props);
            } else {
                element = React.createElement('div');
            }
            return (React.createElement('div', { className: 'platform-element' }, element));
        },
    });

    this.getProps = function() {
        var props = Utils.parseQueryString(window.location.search, true);
        var name  = this.getComponentNameFor(props.action);
        props['componentName'] = (Radix.Components.has(name)) ? name : null;
        return props;
    },

    this.getTarget = function() {
        var jqObj = $(this.config.target);
        return (jqObj.length) ? jqObj : undefined;
    },

    this.propsAreValid = function(props) {
        return (props.componentName) ? true : false;
    },

    this.render = function() {

        var props = this.getProps();
        if (false === this.propsAreValid(props)) {
            Debugger.error('ActionHandlerModule', 'No action found in query string, or action key is invalid. Skipping render');
        }

        var element = React.createElement(this.module, props)
        var jqObj   = this.getTarget();
        if (jqObj) {
            // Render to the in-line element.
            React.render(element, jqObj[0]);
        } else {
            // Use the modal.
            Radix.ModalModule.modal.setState({ contents: element });
            Radix.ModalModule.modal.show();
        }
    }

    this.getComponentNameFor = function(key) {
        var name = 'Action';
        var parts = key.split('-');
        for (var i = 0; i < parts.length; i++) {
            name = name + Utils.ucFirst(parts[i]);
        }
        return name;
    }
}
