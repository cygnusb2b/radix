React.createClass({ displayName: 'ComponentModalLink',

    getDefaultProps: function() {
        return {
            tagName       : 'a',
            wrappingTag   : 'p',
            wrappingClass : null,
            className     : null,
            label         : 'Link',
            prefix        : null,
            suffix        : null,
            contents      : null,
            showLoggedIn  : true,
            showLoggedOut : true
        };
    },

    handleClick: function(event) {
        event.preventDefault();

        Debugger.log('ComponentModalLink', 'handleClick()', this);

        Radix.ModalModule.modal.setState({ contents: this.props.contents });
        Radix.ModalModule.modal.show();
    },

    render: function() {
        Debugger.log('ComponentModalLink', 'render()', this);

        return (
            React.createElement(Radix.Components.get('CustomerBoundLink'), {
                tagName       : this.props.tagName,
                wrappingTag   : this.props.wrappingTag,
                wrappingClass : this.props.wrappingClass,
                className     : this.props.className,
                label         : this.props.label,
                prefix        : this.props.prefix,
                suffix        : this.props.suffix,
                showLoggedIn  : this.props.showLoggedIn,
                showLoggedOut : this.props.showLoggedOut,
                onClick       : this.handleClick
            })
        );
    }
});
