React.createClass({ displayName: 'ComponentForm',

    getDefaultProps: function() {
        return {
            locked      : false,
            className   : 'database-form',
            autocomplete: false,
            buttonLabel : 'Submit',
            onSubmit    : function(event) { event.preventDefault(); Debugger.error('ComponentForm', 'Nothing handled the submit action.') }
        };
    },

    getFormProps: function() {
        return {
            className   : this.props.className,
            onSubmit    : this.props.onSubmit,
            autoComplete: this.props.autocomplete
        };
    },

    getLockedElement: function() {
        if (this.props.locked) {
            return React.createElement('div', {className: 'form-lock'}, React.createElement('i', {className: 'pcfa pcfa-spinner pcfa-5x pcfa-pulse'}));
        }

    },

    render: function() {
        return (
            React.createElement('form', this.getFormProps(),
                this.props.children,
                React.createElement('button', { type: 'submit'}, this.props.buttonLabel),
                this.getLockedElement()
            )
        )
    }
});
