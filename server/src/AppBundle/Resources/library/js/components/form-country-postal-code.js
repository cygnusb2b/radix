React.createClass({ displayName: 'ComponentFormCountryPostalCode',
  getDefaultProps: function() {
    return {
      postalCode: { name: null, value: null },
      countryCode: { name: null, value: null },
      required: false,
      onChange: null,
    };
  },

  render: function() {
    return (
      React.createElement('div', null,
        this._buildCountryElement(),
        this._buildPostalCodeElement()
      )
    );
  },

  updateFieldValue: function(event) {
    if (this.props.countryCode.name == event.target.name) {
      // Country field was changed. Also wipe the postal code.
      this.updateFieldValue({ target: { name: this.props.postalCode.name, value: '' } });
    }
    if ('function' === typeof this.props.onChange) {
      // Send parent event.
      this.props.onChange(event);
    }
  },

  _buildCountryElement: function() {
    return React.createElement(Radix.Components.get('FormSelectCountry'), {
      name: this.props.countryCode.name,
      selected: this.props.countryCode.value,
      wrapperClass: 'countryCode',
      required: this.props.required,
      onChange: this.updateFieldValue
    });
  },

  _buildPostalCodeElement: function() {
    var code = this.props.countryCode.value;
    var element;
    if ('USA' === code || 'CAN' === code) {
      return React.createElement(Radix.Components.get('FormInputText'), {
        label: 'Zip/Postal Code',
        name: this.props.postalCode.name,
        value: this.props.postalCode.value,
        wrapperClass: 'postalCode',
        required: this.props.required,
        onChange: this.updateFieldValue
      });
    }
    return element;
  }
});
