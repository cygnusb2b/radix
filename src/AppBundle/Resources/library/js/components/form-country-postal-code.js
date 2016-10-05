React.createClass({ displayName: 'ComponentFormCountryPostalCode',

    componentWillReceiveProps: function(props) {
        this.setState({ countryCode: props.countryCode});
    },

    getDefaultProps: function() {
        return {
            postalCode  : null,
            countryCode : null,
            onChange    : null,
            fieldRef   : null,
        };
    },

    getInitialState: function() {
        return {
            countryCode: this.props.countryCode
        };
    },

    handleCountryChange: function(event) {
        this.setState({ countryCode: event.target.value });
        if (Utils.isFunction(this.props.onChange)) {
            this.props.onChange(event);
        }
    },

    render: function() {
        return (
            React.createElement('div', null,
                React.createElement(Radix.Components.get('FormSelectCountry'), { fieldRef: this.props.fieldRef, onChange: this.handleCountryChange, selected: this.state.countryCode, wrapperClass: 'countryCode', name: 'customer:primaryAddress.countryCode' }),
                this._buildDependentElement()
            )
        );
    },

    _buildDependentElement: function() {
        var code = this.state.countryCode;
        var element;
        if ('USA' === code || 'CAN' === code) {
            element = React.createElement(Radix.Components.get('FormInputText'), { ref: this.props.fieldRef, onChange: this.props.onChange, name: 'customer:primaryAddress.postalCode', wrapperClass: 'postalCode', label: 'Zip/Postal Code', value: this.props.postalCode });
        } else {
            element = React.createElement(Radix.Components.get('FormInputHidden'), { ref: this.props.fieldRef, onChange: this.props.onChange, name: 'customer:primaryAddress.postalCode', value: this.props.postalCode });
        }
        return element;
    }
});
