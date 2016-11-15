React.createClass({ displayName: 'ComponentFormCountryPostalCode',

    componentWillReceiveProps: function(props) {
        this.setState({ countryCode: props.countryCode, postalCode: props.postalCode });
    },

    getDefaultProps: function() {
        return {
            postalCode  : null,
            countryCode : null,
            required    : false,
            onChange    : null,
            fieldRef    : null,
        };
    },

    getInitialState: function() {
        return {
            countryCode : this.props.countryCode,
            postalCode  : this.props.postalCode
        };
    },

    handleCountryChange: function(event) {
        var code = event.target.value;
        this.setState({ countryCode: code, postalCode: null });

        if (Utils.isFunction(this.props.onChange)) {
            this.props.onChange(event);
        }
    },

    render: function() {
        return (
            React.createElement('div', null,
                React.createElement(Radix.Components.get('FormSelectCountry'), { required: this.props.required, fieldRef: this.props.fieldRef, onChange: this.handleCountryChange, selected: this.state.countryCode, wrapperClass: 'countryCode', name: 'identity:primaryAddress.countryCode' }),
                this._buildDependentElement()
            )
        );
    },

    _buildDependentElement: function() {
        var code = this.state.countryCode;
        var element;
        if ('USA' === code || 'CAN' === code) {
            element = React.createElement(Radix.Components.get('FormInputText'), { required: this.props.required, ref: this.props.fieldRef, onChange: this.props.onChange, name: 'identity:primaryAddress.postalCode', wrapperClass: 'postalCode', label: 'Zip/Postal Code', value: this.state.postalCode });
        } else {
            element = React.createElement(Radix.Components.get('FormInputHidden'), { ref: this.props.fieldRef, onChange: this.props.onChange, name: 'identity:primaryAddress.postalCode', value: this.state.postalCode });
        }
        return element;
    }
});
