React.createClass({ displayName: 'FormProductEmail',

    getDefaultProps: function() {
        return {
            optedIn     : false,
            productId   : null,
            productKey  : null,
            productName : null,
            description : null,
            onChange    : null,
            fieldRef    : null
        };
    },

    render: function() {
        var options = [
            { label: 'No', value: 'false' },
            { label: 'Yes', value: 'true' }
        ];
        var optedIn = (this.props.optedIn) ? 'true' : 'false';
        return (
            React.createElement('div', { className: 'form-product-email' },
                React.createElement(Radix.Components.get('FormRadios'), {
                    name     : 'submission:optIns.' + this.props.productId,
                    label    : 'Subscribe',
                    selected : optedIn,
                    options  : options,
                    className: 'form-element-field toggle',
                    onChange : this.props.onChange,
                    ref      : this.props.fieldRef
                }),
                React.createElement(Radix.Components.get('ProductEmailDeployment'), {
                    id          : this.props.productId,
                    productKey  : this.props.productKey,
                    name        : this.props.productName,
                    description : this.props.description
                })
            )
        );
    },
});
