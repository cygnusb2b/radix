React.createClass({ displayName: 'ComponentModalLinkResetPasswordGenerate',

    getDefaultProps: function() {
        return {
            tagName       : 'a',
            wrappingTag   : 'p',
            wrappingClass : 'text-center',
            className     : null,
            label         : 'Reset',
            prefix        : 'Forgot your password?',
            suffix        : 'it.',
            title         : 'Reset Password'
        };
    },

    onSuccess: function() {
        Radix.ModalModule.modal.hide();
    },

    render: function() {
        Debugger.log('ComponentModalLinkResetPasswordGenerate', 'render()', this);
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
                contents      : React.createElement(Radix.Components.get('ResetPasswordGenerate'), {
                    title     : this.props.title,
                    onSuccess : this.onSuccess
                })
            })
        )
    }
});
