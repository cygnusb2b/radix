React.createClass({ displayName: 'ComponentModalLinkLogin',

    getDefaultProps: function() {
        return {
            tagName       : 'a',
            wrappingTag   : 'p',
            wrappingClass : null,
            className     : null,
            label         : 'Login',
            prefix        : null,
            suffix        : null,
            title         : 'Log In'
        };
    },

    onSuccess: function() {
        Radix.ModalModule.modal.hide();
    },

    render: function() {
        Debugger.log('ComponentModalLinkLogin', 'render()', this);
        return (
            React.createElement(Radix.Components.get('ModalLink'), {
                tagName       : this.props.tagName,
                wrappingTag   : this.props.wrappingTag,
                wrappingClass : this.props.wrappingClass,
                className     : this.props.className,
                label         : this.props.label,
                prefix        : this.props.prefix,
                suffix        : this.props.suffix,
                showLoggedIn  : false,
                contents      : React.createElement(Radix.Components.get('Login'), {
                    title     : this.props.title,
                    onSuccess : this.onSuccess
                })
            })
        )
    }
});
