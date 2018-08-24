React.createClass({ displayName: 'ComponentFormRadios',


    componentWillReceiveProps: function(props) {
        // Handle the selected value.
        var value = null;
        if (props.selected) {
            value = props.selected;
        }
        this.setState({ value: value});
    },

    getDefaultProps: function() {
        return {
            className    : 'form-element-field',
            name         : 'unknown',
            label        : null,
            disabled     : false,
            selected     : null,
            options      : [],
            onChange     : null,
            wrapperClass : null,
            ref          : null
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
            var value = option.value || null;
            var optionProps = {
                name     : this.props.name,
                type     : 'radio',
                value    : value,
                id       : value+'-'+this.props.name,
                disabled : this.props.disabled,
                checked  : this.state.value === value,
                onChange : this.handleChange,
                ref      : this.props.ref
            };
            var label = option.label || 'Label';
            return React.createElement('input', optionProps,
                React.createElement('label', { htmlFor: value+'-'+this.props.name }, label)
            )
        }.bind(this));
    },

    getGroupProps: function() {
        return {
            id        : 'form-element-field-' + this.props.name,
            className : this.props.className
        };
    },

    handleChange: function(event) {
        this.setState({ value: event.target.value });
        if (Utils.isFunction(this.props.onChange)) {
            this.props.onChange(event);
        }
    },

    render: function() {
        var label = this.props.label || Utils.titleize(this.props.name);
        return (
            React.createElement(Radix.Components.get('FormFieldWrapper'), { className: this.props.wrapperClass },
                React.createElement(Radix.Components.get('FormLabel'), { id: this.props.id, value: label }),
                React.createElement('div', this.getGroupProps(),
                    this.getOptions()
                )
            )
        )
    }
});
