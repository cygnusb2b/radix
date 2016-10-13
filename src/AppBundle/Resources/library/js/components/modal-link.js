React.createClass({ displayName: 'ComponentModalLink',

    getDefaultProps: function() {
        return {
            tagName       : 'a',
            className     : null,
            label         : 'Link',
            contents      : null,
            showLoggedIn  : true,
            showLoggedOut : true
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

        Debugger.info('ComponentModalLink', 'handleClick()', this);

        Radix.ModalModule.modal.setState({ contents: this.props.contents });
        Radix.ModalModule.modal.show();
    },

    render: function() {
        Debugger.info('ComponentModalLink', 'render()', this);

        if (!this.state.visible) {
            return (React.createElement('span'));
        }

        return (React.createElement(this.props.tagName, {
            style     : { cursor: 'pointer' },
            className : this.props.className,
            onClick   : this.handleClick
        }, this.props.label));
    }
});
