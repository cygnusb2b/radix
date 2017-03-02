React.createClass({ displayName: 'FormInquiry',
  // @todo:: Should these forms take the account object directly, or simply the form field values??
  getDefaultProps: function() {
    return {
      account  : {},
      values   : {},
      onChange : null,
      onSubmit : null,
    }
  },

  render: function() {
    return (this._getForm())
  },

  _findAnswerFor: function(key) {
    return this.props.values[key];
  },

  _getForm: function() {
    // @todo This should likely be handled by the form definitions.
    var account      = this.props.account;
    var disableEmail = (account._id) ? true : false;
    var phoneType    = account.primaryPhone.phoneType || 'Phone';
    var phoneLabel   = phoneType + ' #';

    return React.createElement('form', { autocomplete: false, className: 'database-form', onSubmit: this.props.onSubmit },
      React.createElement(Radix.Components.get('FormInputHidden'), { name: 'identity:primaryAddress.identifier', value: this.props.values['identity:primaryAddress.identifier'] }),
      React.createElement(Radix.Components.get('FormInputHidden'), { name: 'identity:primaryPhone.identifier', value: this.props.values['identity:primaryPhone.identifier'] }),
      React.createElement(Radix.Components.get('FormInputText'), { onChange: this.props.onChange, name: 'identity:givenName', wrapperClass: 'givenName', label: 'First Name', required: true, value: this.props.values['identity:givenName'] }),
      React.createElement(Radix.Components.get('FormInputText'), { onChange: this.props.onChange, name: 'identity:familyName', wrapperClass: 'familyName', label: 'Last Name', required: true, value: this.props.values['identity:familyName'] }),
      React.createElement(Radix.Components.get('FormInputText'), { onChange: this.props.onChange, type: 'email', name: 'identity:primaryEmail', wrapperClass: 'email', label: 'Email Address', required: !disableEmail, readonly: disableEmail, value: this.props.values['identity:primaryEmail'] }),
      React.createElement(Radix.Components.get('FormInputText'), { onChange: this.props.onChange, type: 'tel', name: 'identity:primaryPhone.number', wrapperClass: 'phone', label: phoneLabel, value: this.props.values['identity:primaryPhone.number'] }),
      React.createElement(Radix.Components.get('FormInputText'), { onChange: this.props.onChange, name: 'identity:companyName', wrapperClass: 'companyName', label: 'Company Name', value: this.props.values['identity:companyName'] }),
      React.createElement(Radix.Components.get('FormInputText'), { onChange: this.props.onChange, name: 'identity:title', wrapperClass: 'title', label: 'Job Title', required: true, value: this.props.values['identity:title'] }),
      React.createElement(Radix.Components.get('CountryPostalCode'), { onChange: this.props.onChange, postalCode: { name: 'identity:primaryAddress.postalCode', value: this.props.values['identity:primaryAddress.postalCode'] }, countryCode: { name: 'identity:primaryAddress.countryCode', value: this.props.values['identity:primaryAddress.countryCode'] }, required: false }),
      React.createElement(Radix.Components.get('FormQuestion'), { onLookup: this._findAnswerFor, onChange: this.props.onChange, questionId: '580f6cff39ab465c2caf74ad', value: this.props.values['submission:answers.580f6cff39ab465c2caf74ad'] }),
      React.createElement(Radix.Components.get('FormQuestion'), { onLookup: this._findAnswerFor, onChange: this.props.onChange, questionId: '583c410839ab46dd31cbdf6d', value: this.props.values['identity:answers.583c410839ab46dd31cbdf6d'], required: false }),
      React.createElement(Radix.Components.get('FormQuestion'), { onLookup: this._findAnswerFor, onChange: this.props.onChange, questionId: '580f6b3bd78c6a78830041bb', value: this.props.values['identity:answers.580f6b3bd78c6a78830041bb'], required: true }),
      React.createElement(Radix.Components.get('FormQuestion'), { onLookup: this._findAnswerFor, onChange: this.props.onChange, questionId: '580f6d056cdeea4730ddbb2c', value: this.props.values['submission:answers.580f6d056cdeea4730ddbb2c'] }),
      React.createElement('button', { type: 'submit'}, 'Submit')
    );
  }
});
