React.createClass({ displayName: 'ComponentForm',

    getDefaultProps: function() {
        return {
            locked      : false,
            className   : 'database-form',
            autocomplete: false,
            buttonLabel : 'Submit',
            onSubmit    : null
        };
    },

    getFormProps: function() {
        return {
            className   : this.props.className,
            onSubmit    : this.handleSubmit,
            autoComplete: this.props.autocomplete
        };
    },

    getLockedElement: function() {
        if (this.props.locked) {
            return React.createElement('div', {className: 'form-lock'}, React.createElement('i', {className: 'pcfa pcfa-spinner pcfa-5x pcfa-pulse'}));
        }
    },

    handleSubmit: function(event) {
        event.preventDefault();
        var handler = this.props.onSubmit;
        if (true === Utils.isFunction(hander)) {
            handler(event);
        } else {
            Debugger.error('ComponentForm', 'Nothing handled the submit action.');
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
