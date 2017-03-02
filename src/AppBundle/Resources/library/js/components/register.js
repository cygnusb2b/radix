React.createClass({ displayName: 'ComponentRegister',
  getDefaultProps: function() {
    return {
      title: 'Register',
      description: null,
      className: null,
      referringPath: null,
      onSuccess: null,
      onFailure: null
    };
  },

  getFormDefinition: function() {
    // @todo The backend should dictate these settings.
    return [
      // The backend should automatically add these if an address or phone field is displayed below.
      { component: 'FormInputHidden', name: 'identity:primaryAddress.identifier' },

      { component: 'FormInputText', type: 'text',     name: 'identity:givenName',    wrapperClass: 'givenName',   label: 'First Name',    required: true },
      { component: 'FormInputText', type: 'text',     name: 'identity:familyName',   wrapperClass: 'familyName',  label: 'Last Name',     required: true },
      { component: 'FormInputText', type: 'email',    name: 'identity:primaryEmail', wrapperClass: 'email',       label: 'Email Address', required: true },
      { component: 'FormInputText', type: 'password', name: 'identity:password',     wrapperClass: 'password',    label: 'Password',      required: true },
      { component: 'FormInputText', type: 'text',     name: 'identity:companyName',  wrapperClass: 'companyName', label: 'Company Name',  required: true },
      { component: 'FormInputText', type: 'text',     name: 'identity:title',        wrapperClass: 'title',       label: 'Job Title',     required: true },

      // The backend should use this by default when selecting country??
      { component: 'CountryPostalCode', postalCode: 'identity:primaryAddress.postalCode', countryCode: 'identity:primaryAddress.countryCode', required: true },

      // The backend simply needs to know the question id - the boundTo will be generated from that.
      // Ultimately could build a local storage cache for these, so questions do not need to be requested on each page.
      // For starters, just caching between questions would probably be helpful.
      { component: 'FormQuestion', questionId: '583c410839ab46dd31cbdf6d', boundTo: 'identity', required: true },
      { component: 'FormQuestion', questionId: '580f6b3bd78c6a78830041bb', boundTo: 'identity', required: true }
    ];
  },

  getInitialState: function() {
    return {
      loggedIn: AccountManager.isLoggedIn(),
      values: AccountManager.getAccountValues(),
      verify: null
    };
  },

  updateFieldValue: function(event) {
    var stateSlice = this.state.values;
    stateSlice[event.target.name] = event.target.value;
    this.setState({ values: stateSlice });
  },

  handleSubmit: function(event) {
    event.preventDefault();

    var formData = this.state.values;

    var locker = this._formLock;
    var error  = this._error;

    error.clear();
    locker.lock();

    var referringHost = window.location.protocol + '//' + window.location.host;
    var referringHref = window.location.href;
    if (Utils.isString(this.props.referringPath)) {
      referringHref = referringHost + '/' + this.props.referringPath.replace(/^\//, '');
    }

    formData['submission:referringHost'] = referringHost;
    formData['submission:referringHref'] = referringHref;

    var payload   = {
      data: formData,
      meta: this.props.meta || {},
      notify : Utils.isObject(this.props.notify) ? this.props.notify : {}
    };

    Debugger.info('ComponentRegister', 'handleSubmit()', payload);

    if (false === this._validateSubmit(formData)) {
      locker.unlock();
      return;
    }

    AccountManager.register(payload).then(function(response) {
      locker.unlock();
      var verify = {
        emailAddress : response.data.email,
        accountId    : response.data.account
      };
      this.setState({ verify: verify });
    }.bind(this), function(response) {
      locker.unlock();
      error.displayAjaxError(response);
    });
  },

  render: function() {
    Debugger.log('ComponentRegister', 'render()', this);

    var elements = (this.state.loggedIn) ? this._getLoggedInElements() : this._getLoggedOutElements();
    return (
      React.createElement('div', { className: 'login-list' },
        React.createElement('h2', { className: 'name' }, this.props.title),
        elements
      )
    );
  },

  _getLoggedInElements: function() {
    return React.createElement('div', null,
      React.createElement('h5', null, 'You are currently logged in.')
    );
  },

  _getLoggedOutElements: function() {
    var elements;
    if (!this.state.verify) {
      elements = React.createElement('div', null,
        React.createElement(Radix.Components.get('Form'), {
          name: 'register',
          fields: this.getFormDefinition(),
          values: this.state.values,
          onChange: this.updateFieldValue,
          onSubmit: this.handleSubmit
        }),
        React.createElement('p', { className: 'text-center' }, 'Already have an account? ',
          React.createElement(Radix.Components.get('ModalLinkLogin'), { label: 'Sign in!' })
        ),
        React.createElement(Radix.Components.get('FormErrors'), { ref: this._setErrorDisplay }),
        React.createElement(Radix.Components.get('FormLock'),   { ref: this._setLock })
      );
    } else {
      elements = React.createElement('div', null,
        React.createElement(Radix.Components.get('RegisterVerify'), this.state.verify)
      );
    }
    return elements;
  },

  _formRefs: {},

  _setErrorDisplay: function(ref) {
    this._error = ref;
  },

  _setLock: function(ref) {
    this._formLock = ref;
  },

  _validateSubmit: function(data) {
    var error = this._error;
    if (!data['identity:password']) {
      error.display('The password field is required.');
      return false;
    }
    if (data['identity:password'].length < 4) {
      error.display('The password must be at least 4 characters long.');
      return false;
    }
    if (data['identity:password'].length > 72) {
      error.display('The password cannot be longer than 72 characters.');
      return false;
    }
    return true;
  }
});
