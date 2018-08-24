React.createClass({ displayName: 'PrivacyPolicy',

    getDefaultProps: function() {
        return {
            className : 'database-form text-center',
            message : 'By submitting this form, I accept that the data provided on this form will be processed, stored and used in accordance with the terms set out in our <a href="/privacy-policy" target="_blank">privacy policy.</a>'
        };
    },

    render: function() {
        var elements = this._getPolicyElements();
        return (elements)
    },

    _getPolicyElements: function() {
        if (Application.settings.privacyPolicy.html) {
            return React.createElement('p', { className: this.props.className,
                dangerouslySetInnerHTML: { __html: Application.settings.privacyPolicy.html }
            })
        } else {
            return React.createElement('p', { className: this.props.className,
                dangerouslySetInnerHTML: { __html: this.props.message }
            })
        }
    }

});