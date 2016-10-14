React.createClass({ displayName: 'ComponentModalLoginLink',

    getDefaultProps: function() {
        return {
            tagName       : 'a',
            className     : null,
            label         : 'Login',

            showLoggedIn  : true,
            showLoggedOut : true
        };
    },

    render: function() {
        Debugger.log('ComponentModalLoginLink', 'render()', this);

        if (!this.state.visible) {
            return (React.createElement('span'));
        }

        return (React.createElement())

        return (React.createElement(this.props.tagName, {
            style     : { cursor: 'pointer' },
            className : this.props.className,
            onClick   : this.handleClick
        }, this.props.label));
    }
});
