React.createClass({ displayName: 'ComponentInquiry',

    getDefaultProps: function() {
        // @todo - generic forms should always have these fields of some sort... check the other form components to consolidate.
        return {
            title           : 'Request More Information',
            description     : null,
            className       : null,
            modelType       : null,
            modelIdentifier : null,
            notify          : {}, // Technically the notify value could be an array of notification objects.
            successRedirect : null,
            referringPath   : null
        };
    },

    getFormDefinition: function() {
      // @todo The backend should dictate these settings.
      var account      = this.state.account;
      var disableEmail = (account._id) ? true : false;
      var phoneType    = account.primaryPhone.phoneType || 'Phone';
      var phoneLabel   = phoneType + ' #';
      return [
        // The backend should automatically add these if an address or phone field is displayed below.
        { component: 'FormInputHidden', name: 'identity:primaryAddress.identifier' },
        { component: 'FormInputHidden', name: 'identity:primaryPhone.identifier' },

        { component: 'FormInputText', type: 'text',  name: 'identity:givenName',           wrapperClass: 'givenName',   label: 'First Name',    required: true  },
        { component: 'FormInputText', type: 'text',  name: 'identity:familyName',          wrapperClass: 'familyName',  label: 'Last Name',     required: true  },
        { component: 'FormInputText', type: 'email', name: 'identity:primaryEmail',        wrapperClass: 'email',       label: 'Email Address', required: !disableEmail, readonly: disableEmail },
        { component: 'FormInputText', type: 'tel',   name: 'identity:primaryPhone.number', wrapperClass: 'phone',       label: phoneLabel,    },
        { component: 'FormInputText', type: 'text',  name: 'identity:companyName',         wrapperClass: 'companyName', label: 'Company Name' },
        { component: 'FormInputText', type: 'text',  name: 'identity:title',               wrapperClass: 'title',       label: 'Job Title',     required: true  },

        // The backend should use this by default when selecting country??
        { component: 'CountryPostalCode', postalCode: 'identity:primaryAddress.postalCode', countryCode: 'identity:primaryAddress.countryCode', required: true },

        // The backend simply needs to know the question id - the boundTo will be generated from that.
        // Ultimately could build a local storage cache for these, so questions do not need to be requested on each page.
        // For starters, just caching between questions would probably be helpful.
        { component: 'FormQuestion', questionId: '580f6cff39ab465c2caf74ad', boundTo: 'submission' },
        { component: 'FormQuestion', questionId: '583c410839ab46dd31cbdf6d', boundTo: 'identity', required: false },
        { component: 'FormQuestion', questionId: '580f6b3bd78c6a78830041bb', boundTo: 'identity', required: true },
        { component: 'FormQuestion', questionId: '580f6d056cdeea4730ddbb2c', boundTo: 'submission' }
      ];
    },

    componentDidMount: function() {
      EventDispatcher.subscribe('AccountManager.account.loaded', function() {
        this.setState({ account : AccountManager.getAccount(), values: AccountManager.getAccountValues() });
      }.bind(this));

      EventDispatcher.subscribe('AccountManager.account.unloaded', function() {
          this.setState({ account : AccountManager.getAccount(), values: AccountManager.getAccountValues(), nextTemplate: null });
      }.bind(this));
    },

    getInitialState: function() {
      return {
        account: AccountManager.getAccount(),
        values: AccountManager.getAccountValues(),
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

        var sourceKey = 'inquiry';
        var payload   = {
            data: formData,
            meta: {
                model  : {
                    type       : this.props.modelType,
                    identifier : this.props.modelIdentifier
                }
            },
            notify : Utils.isObject(this.props.notify) ? this.props.notify : {}
        };

        Debugger.info('InquiryModule', 'handleSubmit', sourceKey, payload);

        Ajax.send('/app/submission/' + sourceKey, 'POST', payload).then(function(response, xhr) {
          locker.unlock();
          if (Utils.isString(this.props.successRedirect)) {
            // Redirect the user.
            window.location.replace(this.props.successRedirect);
          } else {
            locker.unlock();
            // Refresh the account, if logged in.
            if (AccountManager.isLoggedIn()) {
              AccountManager.reloadAccount().then(function() {
                EventDispatcher.trigger('AccountManager.account.loaded');
              });
            }
            // Set the next template to display.
            this.setState({ nextTemplate: template });
          }
        }.bind(this), function(jqXHR) {
          locker.unlock();
          error.displayAjaxError(jqXHR);
        });
    },

    render: function() {
        Debugger.log('ComponentInquiry', 'render()', this);

        var className = 'platform-element';
        if (this.props.className) {
            className = className + ' ' + this.props.className;
        }
        var elements;
        if (this.state.nextTemplate) {
            elements = React.createElement('div', { className: className, dangerouslySetInnerHTML: { __html: this.state.nextTemplate } });
        } else {
            elements = React.createElement('div', { className: className },
                React.createElement('h2', null, this.props.title),
                React.createElement('p', { dangerouslySetInnerHTML: { __html: this.props.description } }),
                React.createElement(Radix.Components.get('ModalLinkLoginVerbose')),
                React.createElement('hr'),
                React.createElement(Radix.Components.get('Form'), {
                    name: 'inquiry',
                    fields: this.getFormDefinition(),
                    values: this.state.values,
                    onChange: this.updateFieldValue,
                    onSubmit: this.handleSubmit
                }),
                React.createElement(Radix.Components.get('FormErrors'), { ref: this._setErrorDisplay }),
                React.createElement(Radix.Components.get('FormLock'),   { ref: this._setLock })
            );
        }
        return (elements);
    },

    _setErrorDisplay: function(ref) {
        this._error = ref;
    },

    _setLock: function(ref) {
        this._formLock = ref;
    },

});
