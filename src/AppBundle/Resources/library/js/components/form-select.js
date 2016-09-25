React.createClass({ displayName: 'ComponentFormSelect',

    componentWillMount: function() {
        this.insertPlaceholder(this.props);
    },

    componentWillReceiveProps: function(props) {
        if (this.props.options.length !== props.options.length) {
            // The options are going to change. Ensure the placeholder is added again.
            this.insertPlaceholder(props);
        }

        // Handle the selected value.
        var value = null;
        if (props.selected) {
            value = props.selected;
        } else if (this.props.placeholder) {
            value = this.props.placeholder;
        }
        this.setState({ value: value});
    },

    getDefaultProps: function() {
        return {
            className   : 'form-element-field',
            name        : 'unknown',
            disabled    : false,
            label       : null,
            placeholder : 'Please select...',
            selected    : null,
            options     : [],
            onChange    : null,
            wrapperClass: null
        };
    },

    getInitialState: function() {
        return {
            value: this.props.selected
        }
    },

    getOptions: function() {
        return this.props.options.map(function(option) {
            option = Utils.isObject(option) ? option : {};
            var optionProps = {
                value: option.value || null,
                label: option.label || null,
            };
            return React.createElement(Radix.Components.get('FormSelectOption'), optionProps);
        });
    },

    getSelectProps: function() {
        return {
            id        : 'form-element-field-' + this.props.name,
            value     : this.state.value,
            name      : this.props.name,
            className : this.props.className,
            onChange  : this.handleChange,
            disabled  : this.props.disabled
        };
    },

    handleChange: function(event) {
        this.setState({ value: event.target.value });
        if (Utils.isFunction(this.props.onChange)) {
            this.props.onChange(event);
        }
    },

    insertPlaceholder: function(props) {
        if (!this.props.placeholder) {
            return;
        }
        props.options.unshift({
            value: this.props.placeholder,
            label: this.props.placeholder
        });
    },

    render: function() {
        var label = this.props.label || Utils.titleize(this.props.name);
        return (
            React.createElement(Radix.Components.get('FormFieldWrapper'), { name: this.props.name, className: this.props.wrapperClass },
                React.createElement(Radix.Components.get('FormLabel'), { id: this.props.id, value: label }),
                React.createElement('select', this.getSelectProps(),
                    this.getOptions()
                )
            )
        )
    }
});
