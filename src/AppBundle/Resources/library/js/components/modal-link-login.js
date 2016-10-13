React.createClass({ displayName: 'ComponentModalLinkLogin',

    getDefaultProps: function() {
        return {
            tagName       : 'a',
            className     : null,
            label         : 'Login',
            title         : 'Log In'
        };
    },

    onSuccess: function() {
        Radix.ModalModule.modal.hide();
    },

    render: function() {
        Debugger.info('ComponentModalLinkLogin', 'render()', this);
        return (
            React.createElement(Radix.Components.get('ModalLink'), {
                tagName      : this.props.tagName,
                className    : this.props.className,
                label        : this.props.label,
                showLoggedIn : false,
                contents     : React.createElement(Radix.Components.get('Login'), {
                    title     : this.props.title,
                    onSuccess : this.onSuccess
                })
            })
        )
    }
});
