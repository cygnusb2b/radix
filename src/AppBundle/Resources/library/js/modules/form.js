function FormModule()
{
    this.elements = {
        selectOption: function(props) {
            var defaults = {
                value: '',
                label: ''
            };

            $.extend(defaults, props);

            return React.createElement('option', { value: defaults.value }, defaults.label);
        },

        select: function(props) {
            var defaults = {
                wrapperTagName: 'div',
                name: 'unknown',
                label: null,
                value: null,
                required: false,
                disabled: false,
                options: [],
                onChange: null
            };

            $.extend(defaults, props);

            var label = defaults.label || Utils.titleize(defaults.name);
            var inputProps = {
                id: 'form-element-field-' + defaults.name,
                name: defaults.name,
                ref: defaults.name,
                className: 'form-element-field',
                // placeholder: defaults.placeholder || label,
                onChange: function(e) {
                    if ('function' === typeof defaults.onChange) {
                        defaults.onChange(e, this.props);
                    }
                    this.props.value = e.target.value;
                }
            };

            if (defaults.value) inputProps.value = defaults.value;
            if (true === defaults.required) inputProps.required = 'required';
            if (true === defaults.disabled) inputProps.disabled = 'disabled';

            var options = defaults.options.map(function(option) {
                return Radix.FormModule.get('selectOption', option);
            });

            return React.createElement(defaults.wrapperTagName, { className: 'form-element-wrapper '+defaults.name+'' },
                React.createElement('select', inputProps, options),
                React.createElement('label', { htmlFor: inputProps.id, className: 'form-element-label' }, label)
            );

        },

        textField: function(props) {
            var defaults = {
                wrapperTagName: 'div',
                name: 'unknown',
                label: null,
                placeholder: null,
                value: null,
                required: false,
                autofocus: false,
                autocomplete: true,
                disabled: false,
                type: 'text',
                onKeyUp: null,
                onBlur: null
            }
            $.extend(defaults, props);

            var label = defaults.label || Utils.titleize(defaults.name);
            var inputProps = {
                id: 'form-element-field-' + defaults.name,
                name: defaults.name,
                type: defaults.type,
                ref: defaults.name,
                className: 'form-element-field',
                placeholder: defaults.placeholder || label,
                onChange: function(e) {
                    this.props.value = e.target.value;
                }
            };

            if (defaults.value) inputProps.value = defaults.value;
            if (true === defaults.required) inputProps.required = 'required';
            if (true === defaults.autofocus) inputProps.autofocus = 'autofocus';
            if (true === defaults.disabled) inputProps.disabled = 'disabled';
            if (false === defaults.autocomplete) inputProps.autoComplete = 'off';
            if ('function' === typeof defaults.onKeyUp) inputProps.onKeyUp = defaults.onKeyUp;
            if ('function' === typeof defaults.onBlur) inputProps.onBlur = defaults.onBlur;
            return React.createElement(defaults.wrapperTagName, { className: 'form-element-wrapper '+defaults.name+'' },
                React.createElement('input', inputProps),
                React.createElement('label', { htmlFor: inputProps.id, className: 'form-element-label' }, label)
            );

        },
        textArea: function(props) {
            var defaults = {
                wrapperTagName: 'div',
                name: 'unknown',
                label: null,
                placeholder: null,
                value: null,
                rows: 3,
                required: false,
                autofocus: true,
                disabled: false,
                type: 'text',
                onKeyUp: null,
                onBlur: null
            }
            $.extend(defaults, props);

            var label = defaults.label || Utils.titleize(defaults.name);
            var inputProps = {
                id: 'form-element-field-' + defaults.name,
                name: defaults.name,
                type: defaults.type,
                ref: defaults.name,
                rows: defaults.rows,
                className: 'form-element-field',
                placeholder: defaults.placeholder || label,
                onChange: function(e) {
                    this.props.value = e.target.value;
                }
            };

            if (defaults.value) inputProps.value = defaults.value;
            if (true === defaults.required) inputProps.required = 'required';
            if (true === defaults.autofocus) inputProps.autofocus = 'autofocus';
            if (true === defaults.disabled) inputProps.disabled = 'disabled';
            if ('function' === typeof defaults.onKeyUp) inputProps.onKeyUp = defaults.onKeyUp;
            if ('function' === typeof defaults.onBlur) inputProps.onBlur = defaults.onBlur;
            return React.createElement(defaults.wrapperTagName, { className: 'form-element-wrapper '+defaults.name+'' },
                React.createElement('textarea', inputProps),
                React.createElement('label', { htmlFor: inputProps.id, className: 'form-element-label' }, label)
            );

        }
    };

    this.has = function(key)
    {
        return null !== this.get(key);
    }

    this.get = function(key, props)
    {
        props = props || null;
        if (this.elements.hasOwnProperty(key)) {
            return this.elements[key](props);
        }
        return null;
    }
}
