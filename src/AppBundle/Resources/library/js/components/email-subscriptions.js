React.createClass({ displayName: 'ComponentEmailSubscriptions',

  getDefaultProps: function() {
    return {
      title           : 'Manage Email Subscriptions',
      description     : null,
      className       : null,
      notify          : {}, // Technically the notify value could be an array of notification objects.
      successRedirect : null,
      referringPath   : null
    };
  },

  getFormDefinition: function() {
    // @todo The backend should dictate these settings.
    var disableEmail = false; //(account.token) ? true : false;
    var phoneType    = 'Phone'; //account.primaryPhone.phoneType || 'Phone';
    var phoneLabel   = phoneType + ' #';
    return [
      // The backend should automatically add these if an address or phone field is displayed below.
      { component: 'FormInputHidden', name: 'identity:primaryAddress.identifier' },
      { component: 'FormInputHidden', name: 'identity:primaryPhone.identifier' },

      // This is a new scenerio: the value comes from the identity, name is bound to the submission.
      { component: 'FormInputText', type: 'email', name: 'identity:primaryEmail',  wrapperClass: 'email',       label: 'Email Address', required: !disableEmail, readonly: disableEmail },
      { component: 'FormInputText', type: 'text',  name: 'identity:givenName',           wrapperClass: 'givenName',   label: 'First Name',    required: true  },
      { component: 'FormInputText', type: 'text',  name: 'identity:familyName',          wrapperClass: 'familyName',  label: 'Last Name',     required: true  },
      { component: 'FormInputText', type: 'text',  name: 'identity:companyName',         wrapperClass: 'companyName', label: 'Company Name' },
      { component: 'FormInputText', type: 'text',  name: 'identity:title',               wrapperClass: 'title',       label: 'Job Title',     required: true  },

      // The backend should use this by default when selecting country??
      { component: 'CountryPostalCode', postalCode: 'identity:primaryAddress.postalCode', countryCode: 'identity:primaryAddress.countryCode', required: true },

      { component: 'FormInputText', type: 'tel',   name: 'identity:primaryPhone.number', wrapperClass: 'phone',       label: phoneLabel,    },

      // The backend simply needs to know the question id - the boundTo will be generated from that.
      // Ultimately could build a local storage cache for these, so questions do not need to be requested on each page.
      // For starters, just caching between questions would probably be helpful.
      { component: 'FormQuestion', questionId: '583c410839ab46dd31cbdf6d', boundTo: 'identity', required: true },
      { component: 'FormQuestion', questionId: '580f6b3bd78c6a78830041bb', boundTo: 'identity', required: true },
      // Employee Size
      // Sales Volume
      { component: 'FormQuestion', questionId: '580f6b3bd78c6a78830041c7', boundTo: 'identity', required: false },
      { component: 'FormQuestion', questionId: '580f6b3bd78c6a78830041cf', boundTo: 'identity', required: false }
    ];
  },

  componentDidMount: function() {

    this._loadForm('email-subscriptions');

    EventDispatcher.subscribe('AccountManager.account.loaded', function() {
      this.setState({ nextTemplate: null });
      this._loadForm('email-subscriptions');
    }.bind(this));

    EventDispatcher.subscribe('AccountManager.account.unloaded', function() {
      this.setState({ nextTemplate: null });
      this._loadForm('email-subscriptions');
    }.bind(this));
  },

  getInitialState: function() {
    return {
      loaded: false,
      fields: [],
      values: {},
      nextTemplate : null
    }
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

      var sourceKey = 'product-email-deployment-optin';
      var payload   = {
          data: formData,
          meta: this.props.meta || {},
          notify: Utils.isObject(this.props.notify) ? this.props.notify : {}
      };

      Debugger.info('EmailSubscriptionModule', 'handleSubmit', sourceKey, payload);

      Ajax.send('/app/submission/' + sourceKey, 'POST', payload).then(function(response) {
          if (Utils.isString(this.props.successRedirect)) {
            // Redirect the user.
            window.location.href = this.props.successRedirect;
          } else {
            locker.unlock();
            // Refresh the account, if logged in.
            if (AccountManager.isLoggedIn()) {
              AccountManager.reloadAccount().then(function() {
                EventDispatcher.trigger('AccountManager.account.loaded');
              });
            }
            // Set the next template to display.
            var template = (response.data) ? response.data.template || null : null;
            this.setState({ nextTemplate: template });
          }
      }.bind(this), function(jqXHR) {
          locker.unlock();
          this._error.displayAjaxError(jqXHR);
      }.bind(this));
  },

  render: function() {
    Debugger.log('ComponentEmailSubscriptions', 'render()', this);

    var className = 'platform-element';
    if (this.props.className) {
      className = className + ' ' + this.props.className;
    }
    var elements;

    if (this.state.nextTemplate) {
      elements = React.createElement('div', { className: className, dangerouslySetInnerHTML: { __html: this.state.nextTemplate || '' } });
    } else {
      elements = React.createElement('div', { className: className },
        this._getForm(),
        React.createElement(Radix.Components.get('FormErrors'), { ref: this._setErrorDisplay }),
        React.createElement(Radix.Components.get('FormLock'),   { ref: this._setLock })
      );
    }
    return (elements);
  },

  _getForm: function() {
    var form;
    if (this.state.loaded) {
      form = React.createElement('div', null,
        React.createElement('h2', null, this.props.title),
        React.createElement('p', { dangerouslySetInnerHTML: { __html: this.props.description || '' } }),
        React.createElement(Radix.Components.get('ModalLinkLoginVerbose')),
        React.createElement('hr'),
        React.createElement('div', { className: 'email-subscription-wrapper' },
          React.createElement(Radix.Components.get('FormProductsEmail'), {
            values: this.state.values,
            onChange: this.updateFieldValue,
          }),
          React.createElement(Radix.Components.get('Form'), {
            name: 'email-subscriptions', // 'product-email-deployment-optin',
            fields: this.state.fields,
            values: this.state.values,
            onChange: this.updateFieldValue,
            onSubmit: this.handleSubmit
          })
        )
      );
    }
    return form;
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

  _setErrorDisplay: function(ref) {
    this._error = ref;
  },

  _setLock: function(ref) {
    this._formLock = ref;
  }
});
