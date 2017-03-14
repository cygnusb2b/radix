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

  componentDidMount: function() {

    this._loadForm('inquiry');

    EventDispatcher.subscribe('AccountManager.account.loaded', function() {
      this.setState({ nextTemplate: null });
      this._loadForm('inquiry');
    }.bind(this));

    EventDispatcher.subscribe('AccountManager.account.unloaded', function() {
      this.setState({ nextTemplate: null });
      this._loadForm('inquiry');
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
      if (Utils.isString(this.props.successRedirect)) {
        // Redirect the user.
        window.location.href = this.props.successRedirect;
      } else {
        locker.unlock();
        // Set the next template to display.
        var template = (response.data) ? response.data.template || null : null;
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
        React.createElement(Radix.Components.get('Form'), {
          name: 'inquiry',
          fields: this.state.fields,
          values: this.state.values,
          onChange: this.updateFieldValue,
          onSubmit: this.handleSubmit
        })
      );
    }
    return form;
  },

  _setErrorDisplay: function(ref) {
    this._error = ref;
  },

  _setLock: function(ref) {
    this._formLock = ref;
  }
});
