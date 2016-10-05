function QueryStringModule()
{
    this.config = {
        target: '.radix-module-query-string'
    }

    this.module = React.createClass({ displayName: 'QueryStringModule',

        componentDidMount: function() {

        },

        render: function() {
            return (React.createElement(Radix.Components.get('ParseQueryString'), this.props));
        }

    });

    this.getProps = function() {
        var jqObj = this.getTarget();
        if (!jqObj) {
            return {};
        }
        return {
            query  : jqObj.data('query') || null
        };
    },

    this.getTarget = function() {
        var jqObj = $(this.config.target);
        return (jqObj.length) ? jqObj : undefined;
    },

    this.propsAreValid = function(props) {
        return true;
    },

    this.render = function() {
        var jqObj = this.getTarget();
        if (!jqObj) {
            // Element not present.
            Debugger.info('QueryStringModule', 'No target element found on page. Skipping render.');
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
