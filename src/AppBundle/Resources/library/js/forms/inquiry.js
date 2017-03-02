React.createClass({ displayName: 'FormInquiry',
  // @todo:: Should these forms take the account object directly, or simply the form field values??
  getDefaultProps: function() {
    return {
      account  : {},
      values   : {},
      onSubmit : null
    }
  },

  getInitialState: function() {
    // @todo State should probably be handled by the parent component.
    return this.props.values;
  },

  submitForm: function(event) {
    event.preventDefault();
    if ('function' === typeof this.props.onSubmit) {
      this.props.onSubmit(this.state);
    } else {
      throw new Error('Nothing handled the form submit!');
    }
  },

  updateFieldValue: function(event) {
    var stateSlice = {};
    stateSlice[event.target.name] = event.target.value;
    this.setState(stateSlice);
  },

  render: function() {
    return (this._getForm())
  },

  _findAnswerFor: function(key) {
    return this.state[key];
  },

  _getForm: function() {
    // @todo This should likely be handled by the form definitions.
    var account      = this.props.account;
    var disableEmail = (account._id) ? true : false;
    var phoneType    = account.primaryPhone.phoneType || 'Phone';
    var phoneLabel   = phoneType + ' #';

    return React.createElement('form', { autocomplete: false, className: 'database-form', onSubmit: this.submitForm },
      React.createElement(Radix.Components.get('FormInputHidden'), { name: 'identity:primaryAddress.identifier', value: this.state['identity:primaryAddress.identifier'] }),
      React.createElement(Radix.Components.get('FormInputHidden'), { name: 'identity:primaryPhone.identifier', value: this.state['identity:primaryPhone.identifier'] }),
      React.createElement(Radix.Components.get('FormInputText'), { onChange: this.updateFieldValue, name: 'identity:givenName', wrapperClass: 'givenName', label: 'First Name', required: true, value: this.state['identity:givenName'] }),
      React.createElement(Radix.Components.get('FormInputText'), { onChange: this.updateFieldValue, name: 'identity:familyName', wrapperClass: 'familyName', label: 'Last Name', required: true, value: this.state['identity:familyName'] }),
      React.createElement(Radix.Components.get('FormInputText'), { onChange: this.updateFieldValue, type: 'email', name: 'identity:primaryEmail', wrapperClass: 'email', label: 'Email Address', required: !disableEmail, readonly: disableEmail, value: this.state['identity:primaryEmail'] }),
      React.createElement(Radix.Components.get('FormInputText'), { onChange: this.updateFieldValue, type: 'tel', name: 'identity:primaryPhone.number', wrapperClass: 'phone', label: phoneLabel, value: this.state['identity:primaryPhone.number'] }),
      React.createElement(Radix.Components.get('FormInputText'), { onChange: this.updateFieldValue, name: 'identity:companyName', wrapperClass: 'companyName', label: 'Company Name', value: this.state['identity:companyName'] }),
      React.createElement(Radix.Components.get('FormInputText'), { onChange: this.updateFieldValue, name: 'identity:title', wrapperClass: 'title', label: 'Job Title', required: true, value: this.state['identity:title'] }),
      React.createElement(Radix.Components.get('CountryPostalCode'), { onChange: this.updateFieldValue, postalCode: { name: 'identity:primaryAddress.postalCode', value: this.state['identity:primaryAddress.postalCode'] }, countryCode: { name: 'identity:primaryAddress.countryCode', value: this.state['identity:primaryAddress.countryCode'] }, required: false }),
      React.createElement(Radix.Components.get('FormQuestion'), { onLookup: this._findAnswerFor, onChange: this.updateFieldValue, questionId: '580f6cff39ab465c2caf74ad', value: this.state['submission:answers.580f6cff39ab465c2caf74ad'] }),
      React.createElement(Radix.Components.get('FormQuestion'), { onLookup: this._findAnswerFor, onChange: this.updateFieldValue, questionId: '583c410839ab46dd31cbdf6d', value: this.state['identity:answers.583c410839ab46dd31cbdf6d'], required: false }),
      React.createElement(Radix.Components.get('FormQuestion'), { onLookup: this._findAnswerFor, onChange: this.updateFieldValue, questionId: '580f6b3bd78c6a78830041bb', value: this.state['identity:answers.580f6b3bd78c6a78830041bb'], required: true }),
      React.createElement(Radix.Components.get('FormQuestion'), { onLookup: this._findAnswerFor, onChange: this.updateFieldValue, questionId: '580f6d056cdeea4730ddbb2c', value: this.state['submission:answers.580f6d056cdeea4730ddbb2c'] }),
      React.createElement('button', { type: 'submit'}, 'Submit')
    );
  }
});
