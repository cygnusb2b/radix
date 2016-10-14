React.createClass({ displayName: 'ComponentCustomerBoundLink',

    getDefaultProps: function() {
        return {
            tagName       : 'a',
            wrappingTag   : 'p',
            wrappingClass : null,
            className     : null,
            label         : 'Link',
            prefix        : null,
            suffix        : null,
            showLoggedIn  : true,
            showLoggedOut : true,
            onClick       : function(event) { Debugger.error('ComponentCustomerBoundLink', 'Nothing handled the click action.'); }
        };
    },

    getInitialState: function() {
        var visible;
        if (CustomerManager.isLoggedIn()) {
            visible = this.props.showLoggedIn;
        } else {
            visible = this.props.showLoggedOut;
        }
        return {
            visible: visible
        };
    },

    componentDidMount: function() {
        EventDispatcher.subscribe('CustomerManager.customer.loaded', function() {
            this.setState({ visible: this.props.showLoggedIn });
        }.bind(this));

        EventDispatcher.subscribe('CustomerManager.customer.unloaded', function() {
            this.setState({ visible: this.props.showLoggedOut });
        }.bind(this));
    },

    handleClick: function(event) {
        event.preventDefault();
        Debugger.log('ComponentCustomerBoundLink', 'handleClick()', this);
        this.props.onClick(event);
    },

    render: function() {
        Debugger.log('ComponentCustomerBoundLink', 'render()', this);

        if (!this.state.visible) {
            return (React.createElement('span'));
        }

        var props = {
            style     : { cursor: 'pointer' },
            className : this.props.className,
            onClick   : this.handleClick
        };
        if ('a' === this.props.tagName) {
            props['href'] = 'javascript:void(0);';
        }

        var link = React.createElement(this.props.tagName, props, this.props.label);

        if (this.props.prefix || this.props.suffix) {
            return (
                React.createElement(this.props.wrappingTag, { className: this.props.wrappingClass },
                    this.props.prefix, link, this.props.suffix
                )
            );
        }

        return (link);
    }
});
