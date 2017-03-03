React.createClass({ displayName: 'Form',
  getDefaultProps: function() {
    return {
      name: null,
      autocomplete: false,
      buttonLabel: 'Submit',
      className: 'database-form',
      fields: [],
      values: {},
      onChange: null,
      onSubmit: null,
    }
  },

  render: function() {
    var fields = this.props.fields.map(function(field) {
      if (!field.component) {
        return;
      }
      var action = '_build' + field.component + 'Props';
      if ('function' === typeof this[action]) {
        return React.createElement(Radix.Components.get(field.component), this[action](field));
      }
    }.bind(this));

    var classNames = this.props.className + ' ' + this.props.name;
    return (
      React.createElement('form',
        { autocomplete: this.props.autocomplete, className: classNames, onSubmit: this.props.onSubmit },
        fields,
        React.createElement('button', { type: 'submit'}, this.props.buttonLabel)
      )
    );
  },

  _buildCountryPostalCodeProps: function(field) {
    return {
      key: field.postalCode + '-' + field.countryCode,
      postalCode: { name: field.postalCode, value: this.props.values[field.postalCode] },
      countryCode: { name: field.countryCode, value: this.props.values[field.countryCode] },
      onChange: this.props.onChange,
      required: field.required
    }
  },

  _buildFormInputHiddenProps: function(field) {
    var name = field.name;
    return {
      key: name,
      name: name,
      value: this.props.values[name]
    };
  },

  _buildFormInputTextProps: function(field) {
    var name = field.name;
    var valueKey = field.valueKey ? field.valueKey : name;
    return {
      key: name,
      type: field.type,
      name: name,
      value: this.props.values[valueKey],
      onChange: this.props.onChange,
      wrapperClass: field.wrapperClass,
      label: field.label,
      required: field.required,
      readonly: field.readonly
    }
  },

  _buildFormQuestionProps: function(field) {
    var questionId = field.questionId;
    var key = field.boundTo + ':answers.' + questionId;
    return {
      key: key,
      questionId: questionId,
      value: this.props.values[key],
      required: field.required,
      onChange: this.props.onChange,
      onLookup: this._findAnswerFor
    };
  },

  _findAnswerFor: function(key) {
    return this.props.values[key];
  }
});
