React.createClass({ displayName: 'FormComment',

    getDefaultProps: function() {
        return {
            display        : false,
            allowAnonymous : false,
            requireCaptcha : false,
            displayName    : null,
            onSubmit       : function(event)   { Debugger.error('Nothing handled the form submit.');       },
            fieldRef       : function(input)   { Debugger.error('Nothing handled the field reference.');   },
            captchaRef     : function(captcha) { Debugger.error('Nothing handled the captcha reference.'); },
        }
    },

    render: function() {
        return (this._getForm())
    },

    _getForm: function() {
        if (!this.props.display) {
            return React.createElement('div');
        }
        var className = 'database-form';
        if (this.props.className) {
            className = className + ' ' + this.props.className;
        }
        var captcha;
        if (this.props.requireCaptcha) {
            captcha = React.createElement(Radix.Components.get('Recaptcha'), {
                ref : this.props.captchaRef,
            });
        }

        return React.createElement('form', { autocomplete: false, className: className, onSubmit: this.props.onSubmit },
            React.createElement(Radix.Components.get('FormInputText'), {
                ref          : this.props.fieldRef,
                name         : 'displayName',
                value        : this.props.displayName,
                wrapperClass : 'displayName',
                label        : 'Posting As',
                required     : true,
            }),
            React.createElement(Radix.Components.get('FormTextArea'), {
                name        : 'body',
                label       : 'Your Comment',
                wrapperClass: 'inputBody',
                ref         : this.props.fieldRef,
                required    : true,
            }),
            this._getAnonymizeElement(),
            captcha,
            React.createElement('button', { type: 'submit'}, 'Submit')
        );
    },

    _getAnonymizeElement: function() {
        if (!this.props.allowAnonymous) {
            return;
        }
        return React.createElement(Radix.Components.get('FormRadios'), {
            name     : 'anonymize',
            label    : 'Post Anonymously',
            wrapperClass : 'anonymize',
            selected : 'false',
            options  : [
                { value : 'false', label : 'No' },
                { value : 'true', label : 'Yes' },
            ],
            className: 'form-element-field toggle',
            ref      : this.props.fieldRef
        });
    }
});
