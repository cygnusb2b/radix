React.createClass({ displayName: 'ComponentFormInputHidden',

    componentWillReceiveProps: function(props) {
        // Handle the selected value.
        var value = null;
        if (props.value) {
            value = props.value;
        }
        this.setState({ value: value});
    },

    getDefaultProps: function() {
        return {
            className    : 'form-element-field',
            name         : 'unknown',
            type         : 'hidden',
            value        : null,
            onChange     : null,
            ref          : null
        };
    },

    getInitialState: function() {
        return {
            value: this.props.value
        }
    },

    getInputProps: function() {
        var props = {
            id          : 'form-element-field-' + this.props.name,
            value       : this.state.value,
            type        : this.props.type,
            name        : this.props.name,
            className   : this.props.className,
            onChange    : this.handleChange,
            disabled    : this.props.disabled,
            ref         : this.props.ref
        };
        return props;
    },

    handleChange: function(event) {
        this.setState({ value: event.target.value });
        if (Utils.isFunction(this.props.onChange)) {
            this.props.onChange(event);
        }
    },

    render: function() {
        return (
            React.createElement('input', this.getInputProps())
        )
    }
});
