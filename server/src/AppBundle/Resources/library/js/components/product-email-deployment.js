React.createClass({ displayName: 'ComponentProductEmailDeployment',

    getDefaultProps: function() {
        return {
            className: null,
            id: null,
            productKey: null,
            name: null,
            description: null,
        };
    },

    render: function() {
        return (
            React.createElement('div', { className: this._getClassName() },
                React.createElement('h3', null, this.props.name),
                React.createElement('p', null, this.props.description),
                this.props.children
            )
        )
    },

    _getClassName: function() {
        var className = 'product-email-deployment';
        if (this.props.productKey) {
            className = className + ' ' + this.props.productKey;
        }
        if (this.props.className) {
            className = className + ' ' + this.props.className;
        }
        return className;
    }
});
