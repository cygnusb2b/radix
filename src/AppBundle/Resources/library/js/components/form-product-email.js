React.createClass({ displayName: 'FormProductEmail',

    getDefaultProps: function() {
        return {
            optedIn     : false,
            productId   : null,
            productKey  : null,
            productName : null,
            description : null,
            onChange    : null
        };
    },

    render: function() {
        var options = [
            { label: 'Yes', value: 'true' },
            { label: 'No', value: 'false' }
        ];
        var optedIn = (this.props.optedIn) ? 'true' : 'false';
        return (
            React.createElement('div', { className: 'form-product-email' },
                React.createElement(Radix.Components.get('ProductEmailDeployment'), {
                    id          : this.props.productId,
                    productKey  : this.props.productKey,
                    name        : this.props.productName,
                    description : this.props.description
                }),
                React.createElement(Radix.Components.get('FormRadios'), {
                    name     : 'submission:optIns.' + this.props.productId,
                    label    : 'Subscribe',
                    selected : optedIn,
                    options  : options,
                    onChange : this.props.onChange
                })
            )
        );
    },
});
