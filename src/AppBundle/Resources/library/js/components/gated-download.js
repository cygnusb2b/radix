// @todo Ultimately, specifying a gate should be a seperate concern from the form.
// For example, Radix would be told to gate "something," and that directive would then determine what form to use.
// Gating could be by registration: meaning, if logged in, can view, if not must register
// Gating could be by a form: meaning, a form must be submitted before completion, regardless if logged in (though logged in would pre-pop)
// Gating could by by a product subscription: meaning, a user must be subscribed to "something" in order to view
// The functional response of the form (whether it's to "view" or "download") is simply a different directive, not a different component (which it is now)
// So, this needs to be re-explored.
React.createClass({ displayName: 'ComponentGatedDownload',
  // @todo Should gating simply allow any set of "extra metadata", as opposed to requiring properties to be directly set?
  getDefaultProps: function() {
    return {
      title         : 'Download',
      description   : 'To access this piece of premium content, please verify that the questions below have been answered and are accurate.',
      fileUrl       : null, // The file to download on submit - this should be renamed to `sucessRedirect` to match other forms.
      webhookUrl    : null, // The webhook url to request to retrieve the file (in case you want to hide the url from the component/frontend): note, the hook must utilize CORs
      className     : null,
      referringPath : null,
      notify        : {} // Technically the notify value could be an array of notification objects.
    };
  },


  componentDidMount: function() {

    this._loadForm('gated-download');

    EventDispatcher.subscribe('AccountManager.account.loaded', function() {
      this.setState({ nextTemplate: null });
      this._loadForm('gated-download');
    }.bind(this));

    EventDispatcher.subscribe('AccountManager.account.unloaded', function() {
      this.setState({ nextTemplate: null });
      this._loadForm('gated-download');
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

    formData['submission:fileUrl'] = this.props.fileUrl;

    var sourceKey = 'gated-download';
    var payload   = {
      data: formData,
      meta: this.props.meta || {},
      notify : Utils.isObject(this.props.notify) ? this.props.notify : {}
    };

    Debugger.info('InquiryModule', 'handleSubmit', sourceKey, payload);

    Ajax.send('/app/submission/' + sourceKey, 'POST', payload).then(function(response, xhr) {
      locker.unlock();
      if (Utils.isString(this.props.fileUrl)) {
        // Redirect the user.
        window.location.href = this.props.fileUrl;
      } else {
        // Set the next template to display (thank you page, etc).
        var template = (response.data) ? response.data.template || null : null;
        this.setState({ nextTemplate: template });
      }
    }.bind(this), function(jqXHR) {
      locker.unlock();
      error.displayAjaxError(jqXHR);
    });
  },

  render: function() {
    Debugger.log('ComponentGatedDownload', 'render()', this);

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
          name: 'gated-download',
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
