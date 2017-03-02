React.createClass({ displayName: 'ComponentFormQuestion',

  componentDidMount: function() {
    this._retrieveQuestion(this.props.questionId);
  },

  componentWillReceiveProps: function(nextProps) {
    if (nextProps.questionId !== this.props.questionId) {
      // Re-run the question retrieval.
      this._retrieveQuestion(nextProps.questionId);
    }
  },

  getDefaultProps: function() {
    return {
      questionId: null,
      required: false,
      onChange: null,
      onLookup: null,
      value: null
    };
  },

  getInitialState: function() {
    return {
      loaded   : false,
      question : {}
    };
  },

  render: function() {
    var element = React.createElement('div');
    if (this.state.loaded) {
      element = this._buildElement();
    }
    return (element);
  },

  _buildChildElement: function() {
    var children = this._findChildQuestions();
    var answer   = this.props.value;
    if (!answer) {
      return;
    }
    for (var i = 0; i < children.length; i++) {
      var child = children[i];
      if (answer != child.parentChoiceId) {
        continue;
      }
      var value = this.props.onLookup(child.key);
      return React.createElement(Radix.Components.get('FormQuestion'), { onLookup: this.props.onLookup, onChange: this.props.onChange, questionId: child.id, value: value, required: this.props.required });
    }
  },

  _buildElement: function() {
    var question = this.state.question;
    var type     = question.questionType;
    var answer   = this.props.value;
    var label    = question.label || question.name;

    var element;
    if ('choice-single' === type || 'related-choice-single' === type) {
      var optionKey = 'related-choice-single' === type ? 'relatedChoices' : 'choices';
      var options = [];
      for (var i = 0; i < question[optionKey].length; i++) {
        var choice = question[optionKey][i];
        options.push(choice.option);
      }
      element = React.createElement(Radix.Components.get('FormSelect'), {
        name        : this._createQuestionKey(question),
        label       : label,
        options     : options,
        selected    : answer,
        required    : this.props.required,
        onChange    : this._handleChange,
        wrapperClass: question.key + ' question'
      });
    } else if ('textarea' === type) {
      element = React.createElement(Radix.Components.get('FormTextArea'), {
        name        : this._createQuestionKey(question),
        value       : answer,
        label       : label,
        required    : this.props.required,
        onChange    : this.props.onChange,
        wrapperClass: question.key + ' question'
      });
    } else {
      element = React.createElement('p', null, label);
    }

    return React.createElement('div', { className: 'question-wrapper' }, element, this._buildChildElement());
  },

  _createQuestionKey: function(question) {
    return question.boundTo + ':answers.' + question._id;
  },

  _findChildQuestions: function() {
    var children = [];
    var question = this.state.question;
    if (!question.hasChildQuestions) {
      return children;
    }
    for (var i = 0; i < question.choices.length; i++) {
      var choice = question.choices[i];
      if (!choice.childQuestion) {
        continue;
      }
      var childQuestion = choice.childQuestion;
      children.push({ id: childQuestion._id, parentChoiceId: choice._id, key: this._createQuestionKey(childQuestion) });
    }
    return children;
  },

  _findSelectedChildQuestions: function() {
    var selected = [];
    var children = this._findChildQuestions();
    for (var i = 0; i < children.length; i++) {
      var key = children[i].key;
      if (this.props.onLookup(key)) {
        selected.push(key);
      }
    }
    return selected;
  },

  _handleChange: function(event) {
    // Clear any child answers, since the parent has changed (if applicable).
    var children = this._findSelectedChildQuestions();
    for (var i = 0; i < children.length; i++) {
      this.props.onChange({ target: { name: children[i], value: '' } });
    }
    this.props.onChange(event);
  },

  _retrieveQuestion: function(questionId) {
    Ajax.send('/app/question/' + questionId, 'GET').then(
      function(response) {
        this.setState({ loaded: true, question: response.data });
      }.bind(this),
      function(jqXhr) {
        Debugger.error('Unable to load the question.');
      }.bind(this)
    );
  }
});
