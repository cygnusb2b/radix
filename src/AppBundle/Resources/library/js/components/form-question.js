React.createClass({ displayName: 'ComponentFormQuestion',

  componentDidMount: function() {
    this._retrieveQuestion(this.props.tagKeyOrId, this.props.keyOrId);
  },

  componentWillReceiveProps: function(nextProps) {
    if (nextProps.keyOrId != this.props.keyOrId || nextProps.tagKeyOrId != this.props.keyOrId) {
      // Clear any previosuly selected answer.
      this.setState({ answer: null });
      // Re-run the question retrieval.
      this._retrieveQuestion(nextProps.tagKeyOrId, nextProps.keyOrId);
    }
  },

  getDefaultProps: function() {
    return {
      keyOrId    : null,
      tagKeyOrId : null,
      required   : false,
      answers    : [],
      onChange   : null,
      fieldRef   : null,
      isChild    : false,
    };
  },

  getInitialState: function() {
    return {
      loaded   : false,
      question : {},
      answer   : null
    };
  },

  render: function() {
    var element = React.createElement('div');
    if (this.state.loaded) {
        element = this._buildElement();
    }
    return (element);
  },

  _buildDependentElement: function() {
    if (!this.state.question.hasChildQuestions) {
      return;
    } else if (this.state.answer) {
      var choice = {};
      var question = this.state.question;
      for (var i = 0; i < question.choices.length; i++) {
        if (this.state.answer == question.choices[i]._id) {
          choice = question.choices[i];
          break;
        }
      };

      if (choice._id && choice.childQuestion) {
        return React.createElement(Radix.Components.get('FormQuestion'), { fieldRef: this.props.fieldRef, keyOrId: choice.childQuestion._id, answers: this.props.answers, required: this.props.required, isChild: true });
      }
    }
  },

  _buildElement: function() {
    var question = this.state.question;
    var type     = question.questionType;
    var answer   = this.state.answer;
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
        name        : question.boundTo + ':answers.' + question._id,
        label       : label,
        options     : options,
        selected    : answer,
        required    : this.props.required,
        onChange    : this._handleChoiceSelect,
        wrapperClass: question.key + ' question',
        ref         : this.props.fieldRef
      });
    } else if ('textarea' === type) {
      element = React.createElement(Radix.Components.get('FormTextArea'), {
        name        : question.boundTo + ':answers.' + question._id,
        value       : answer,
        label       : label,
        onChange    : this.props.onChange,
        wrapperClass: question.key + ' question',
        ref         : this.props.fieldRef
      });
    } else {
      element = React.createElement('p', null, label);
    }

    return React.createElement('div', { className: 'question-wrapper' }, element, this._buildDependentElement());
  },

  _extractAnswer: function() {
    var value = null;
    var question = this.state.question;

    if ('identity' !== question.boundTo) {
      return value;
    }

    var questionKey = this.props.isChild ? 'relatedQuestion' : 'question';
    for (var i = 0; i < this.props.answers.length; i++) {
      var answer = this.props.answers[i];
      if (answer[questionKey]._id !== question._id) {
        continue;
      }
      return this._extractAnswerValue(answer);
    };
    return value;
  },

  _extractAnswerValue: function(answer) {
    var value = answer.value;

    if ('identity-answer-choice' === answer._type) {
      if (false === Utils.isObject(value)) {
        return;
      }
      return value._id;
    } else if ('identity-answer-choices' === answer._type) {
      if (value.length < 1) {
        return [];
      }
      var values = [];
      for (var i = 0; i < value.length; i++) {
        values.push(value[i]._id);
      }
      return values;
    }
    return value;
  },

  _handleChoiceSelect: function(event) {
    this.setState({ answer: event.target.value });
    if ('function' === typeof this.props.onChange) {
      this.props.onChange(event);
    }
  },

  _retrieveQuestion: function(tagKeyOrId, keyOrId) {
    var url;
    if (tagKeyOrId) {
      url = '/app/question/tag/' + tagKeyOrId;
    } else if (keyOrId) {
      url = '/app/question/' + keyOrId;
    }
    if (!url) {
      Debugger.error('No question id, key, or tag provided for the question. Unable to retrieve question.');
      return;
    }

    Ajax.send(url, 'GET').then(
      function(response) {
        this.setState({ loaded: true, question: response.data });
        this.setState({ answer: this._extractAnswer() });
      }.bind(this),
      function(jqXhr) {
        Debugger.error('Unable to load the question.');
      }.bind(this)
    );
  }
});
