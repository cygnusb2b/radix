React.createClass({ displayName: 'ComponentFormQuestion',

    componentDidMount: function() {
        var url;
        if (this.props.tagKeyOrId) {
            url = '/app/question/tag/' + this.props.tagKeyOrId;
        } else if (this.props.keyOrId) {
            url = '/app/question/' + this.props.keyOrId;
        }
        if (!url) {
            Debugger.error('No question id, key, or tag provided for the question. Unable to retrieve question.');
            return;
        }

        Ajax.send(url, 'GET').then(
            function(response) {
                this.setState({ loaded: true, question: response.data });
            }.bind(this),
            function(jqXhr) {
                Debugger.error('Unable to load the question.');
            }.bind(this)
        );
    },

    getDefaultProps: function() {
        return {
            keyOrId    : null,
            tagKeyOrId : null,
            answers    : [],
            onChange   : null,
            fieldRef   : null
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

    _buildElement: function() {
        var question = this.state.question;
        var answer   = this._extractAnswer();
        var label    = question.label || question.name;

        var element;
        switch (question.questionType) {
            case 'choice-single':
                var options = [];
                for (var i = 0; i < question.choices.length; i++) {
                    var choice = question.choices[i];
                    options.push(choice.option);
                }
                element = React.createElement(Radix.Components.get('FormSelect'), {
                    name        : question.boundTo + ':answers.' + question._id,
                    label       : label,
                    options     : options,
                    selected    : answer,
                    onChange    : this.props.onChange,
                    wrapperClass: question.key + ' question',
                    ref         : this.props.fieldRef
                });
                break;
            case 'textarea':
                element = React.createElement(Radix.Components.get('FormTextArea'), {
                    name        : question.boundTo + ':answers.' + question._id,
                    value       : answer,
                    label       : label,
                    onChange    : this.props.onChange,
                    wrapperClass: question.key + ' question',
                    ref         : this.props.fieldRef
                });
                break;
            default:
                element = React.createElement('p', null, label);
                break;
        }
        return element;
    },

    _extractAnswer: function() {
        var value = null;
        var question = this.state.question;
        if ('identity' !== question.boundTo) {
            return value;
        }

        for (var i = 0; i < this.props.answers.length; i++) {
            var answer = this.props.answers[i];
            if (answer.question._id !== question._id) {
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
    }
});
