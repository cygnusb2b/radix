React.createClass({ displayName: 'ComponentForm',

    getDefaultProps: function() {
        return {
            className   : 'database-form',
            autocomplete: false,
            onSubmit    : function(event) { event.preventDefault(); Debugger.error('ComponentForm', 'Nothing handled the submit action.') }
        };
    },

    getFormProps: function() {
        var props = {
            className : this.props.className,
            onSubmit  : this.props.onSubmit,
        }
        if (true === this.props.autocomplete) props.autoComplete = 'off';
        return props;
    },

    render: function() {
        return (React.createElement('form', this.getFormProps(), this.props.children))
    }
});
