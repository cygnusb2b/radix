function ActionHandlerModule()
{
    this.config = {
        target: '.radix-module-action-handler'
    }

    this.module = React.createClass({ displayName: 'ActionHandlerModule',

        componentDidMount: function() {

        },

        render: function() {
            var name = this._getComponentName();
            var element;
            if (Radix.Components.has(name)) {
                element = React.createElement(Radix.Components.get(name), this.props);
            } else {
                Debugger.error('No action handler found for ' + this.props.action);
            }
            return (React.createElement('div', { className: 'platform-element' }, element));
        },

        _getComponentName: function() {
            var name = 'Action';
            var parts = this.props.action.split('-');
            for (var i = 0; i < parts.length; i++) {
                name = name + Utils.ucFirst(parts[i]);
            }
            return name;
        }

    });

    this.getProps = function() {
        var jqObj = this.getTarget();
        return Utils.parseQueryString(window.location.search, true);
    },

    this.getTarget = function() {
        var jqObj = $(this.config.target);
        return (jqObj.length) ? jqObj : undefined;
    },

    this.propsAreValid = function(props) {
        return (props.action) ? true : false;
    },

    this.render = function() {
        var jqObj = this.getTarget();
        if (!jqObj) {
            // Element not present.
            Debugger.info('ActionHandlerModule', 'No target element found on page. Skipping render.');
            return;
        }

        var props = this.getProps();
        if (this.propsAreValid(props)) {
            React.render(
                React.createElement(this.module, props),
                jqObj[0]
            );
        }
    }
}
