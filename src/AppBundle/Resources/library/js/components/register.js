React.createClass({ displayName: 'ComponentRegister',
  getDefaultProps: function() {
    return {
      title: 'Title here - Register',
      description: 'Description Goes Here',
      className: null,
      referringPath: null,
      onSuccess: null,
      onFailure: null
    };
  },

  componentDidMount: function() {

    this._loadForm('register');

    EventDispatcher.subscribe('AccountManager.account.loaded', function() {
      this.setState({ nextTemplate: null });
      this._loadForm('register');
    }.bind(this));

    EventDispatcher.subscribe('AccountManager.account.unloaded', function() {
      this.setState({ nextTemplate: null });
      this._loadForm('register');
    }.bind(this));
  },

  getInitialState: function() {
    return {
      loggedIn: AccountManager.isLoggedIn(),
      loaded: false,
      fields: [],
      values: {},
      verify: null
    };
  },

  _loadForm: function(key) {
    var locker = this._formLock;
    locker.lock();

    Ajax.send('/app/form/' + key, 'GET').then(function(response) {
      this.setState({ loaded: true, fields: response.data.form.fields, values: response.data.values });
      locker.unlock();
    }.bind(this), function() {
      locker.unlock();
    });
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
        this._getForm(),
        React.createElement('p', { className: 'text-center' }, 'I accept that the data provided on this form will be processed, stored and used ',
          React.createElement('br', { className: 'text-center' }, 'in accordance with the terms set out in our ',
          React.createElement('a', { href: '/privacy-policy' }, 'privacy policy.')
        )),
        React.createElement('hr'),
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

  _getForm: function() {
    var form;
    if (this.state.loaded) {
      form = React.createElement('div', null,
        React.createElement(Radix.Components.get('Form'), {
          name: 'register',
          fields: this.state.fields,
          values: this.state.values,
          onChange: this.updateFieldValue,
          onSubmit: this.handleSubmit
        })
      );
    }
    return form;
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
