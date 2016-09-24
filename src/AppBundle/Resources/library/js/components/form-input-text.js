React.createClass({ displayName: 'ComponentFormInputText',

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
            disabled     : false,
            required     : false,
            autocomplete : false,
            type         : 'text',
            value        : null,
            label        : null,
            placeholder  : null
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
            placeholder : this.props.placeholder,
            onChange    : this.handleChange,
            disabled    : this.props.disabled,
            required    : this.props.required
        };
        if (false === this.props.autocomplete) props.autoComplete = 'off';
        return props;
    },

    handleChange: function(event) {
        this.setState({ value: event.target.value })
    },

    render: function() {
        var label = this.props.label || Utils.titleize(this.props.name);
        return (
            React.createElement(Radix.Components.get('FormFieldWrapper'), { name: this.props.name },
                React.createElement(Radix.Components.get('FormLabel'), { id: this.props.id, value: label }),
                React.createElement('input', this.getInputProps())
            )
        )
    }
});
