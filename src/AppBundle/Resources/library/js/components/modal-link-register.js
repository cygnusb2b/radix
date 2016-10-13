React.createClass({ displayName: 'ComponentModalLinkRegister',

    getDefaultProps: function() {
        return {
            tagName       : 'a',
            className     : null,
            label         : 'Register',
            title         : 'Sign Up'
        };
    },

    onSuccess: function() {
        Radix.ModalModule.modal.hide();
    },

    render: function() {
        Debugger.info('ComponentModalLinkRegister', 'render()', this);
        return (
            React.createElement(Radix.Components.get('ModalLink'), {
                tagName      : this.props.tagName,
                className    : this.props.className,
                label        : this.props.label,
                showLoggedIn : false,
                contents     : React.createElement(Radix.Components.get('Register'), {
                    title     : this.props.title,
                    onSuccess : this.onSuccess
                })
            })
        )
    }
});
