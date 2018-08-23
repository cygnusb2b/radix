React.createClass({ displayName: 'ComponentLinkLogout',

    getDefaultProps: function() {
        return {
            tagName       : 'a',
            wrappingTag   : 'p',
            wrappingClass : null,
            className     : null,
            label         : 'Logout',
            prefix        : null,
            suffix        : null
        };
    },

    handleClick: function() {
        AccountManager.logout();
    },

    render: function() {
        Debugger.log('ComponentLinkLogout', 'render()', this);
        return (
            React.createElement(Radix.Components.get('AccountBoundLink'), {
                tagName       : this.props.tagName,
                wrappingTag   : this.props.wrappingTag,
                wrappingClass : this.props.wrappingClass,
                className     : this.props.className,
                label         : this.props.label,
                prefix        : this.props.prefix,
                suffix        : this.props.suffix,
                showLoggedOut : false,
                onClick       : this.handleClick
            })
        )
    }
});
