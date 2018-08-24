React.createClass({ displayName: 'ComponentModalLinkRegister',

    getDefaultProps: function() {
        return {
            tagName       : 'a',
            wrappingTag   : 'p',
            wrappingClass : null,
            className     : null,
            label         : 'Register',
            prefix        : null,
            suffix        : null,
            title         : 'Sign Up'
        };
    },

    onSuccess: function() {
        Radix.ModalModule.modal.hide();
    },

    render: function() {
        Debugger.log('ComponentModalLinkRegister', 'render()', this);
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
                contents      : React.createElement(Radix.Components.get('Register'), {
                    title     : this.props.title,
                    onSuccess : this.onSuccess
                })
            })
        )
    }
});
