React.createClass({ displayName: 'ComponentModalLinkLoginVerbose',

    getDefaultProps: function() {
        return {
            tagName       : 'a',
            wrappingTag   : 'p',
            wrappingClass : null,
            className     : null,
            label         : 'login',
            prefix        : 'To complete your request, please provide us with the following information. If you already have an account, you can',
            suffix        : 'to speed up this request.',
            title         : 'Log In'
        };
    },

    render: function() {
        Debugger.log('ComponentModalLinkLoginVerbose', 'render()', this);
        return (
            React.createElement(Radix.Components.get('ModalLinkLogin'), {
                tagName       : this.props.tagName,
                wrappingTag   : this.props.wrappingTag,
                wrappingClass : this.props.wrappingClass,
                className     : this.props.className,
                label         : this.props.label,
                prefix        : this.props.prefix,
                suffix        : this.props.suffix,
                title         : this.props.title
            })
        )
    }
});
