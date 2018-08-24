React.createClass({ displayName: 'ComponentContactSupport',

    getDefaultProps: function() {
        return {
            opening   : 'Having difficulties?',
            className : 'text-center'
        };
    },

    render: function() {
        var elements = this._getSupportElements();
        return (elements)
    },

    _getSupportElements: function() {
        var support = Application.settings.support || {};
        if (!support.email && !support.phone) {
            return React.createElement('span');
        }

        var phoneElement;
        if (support.phone) {
            phoneElement = React.createElement('span', null, ' phone: ',
                React.createElement('a', { href: 'tel:+1' + support.phone}, support.phone)
            );
        }
        var emailElement;
        if (support.email) {
            emailElement = React.createElement('span', null, ' email: ',
                React.createElement('a', { href: 'mailto:' + support.email }, support.email)
            );
        }

        var className = 'support';
        if (this.props.className) {
            className = className + '  ' + this.props.className;
        }

        return React.createElement('p', { className: className },
            this.props.opening, ' Contact our customer support team...',
            React.createElement('br'),
            emailElement, phoneElement
        )
    }
});
