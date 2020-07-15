React.createClass({ displayName: 'FormProductsEmail',

    componentDidMount: function() {
        Ajax.send('/app/product/email-deployment', 'GET').then(
            function(response) {
                this.setState({ loaded: true, products: response.data });
            }.bind(this),
            function(jqXhr) {
                Debugger.error('Unable to load products.');
            }.bind(this)
        );
    },

    getDefaultProps: function() {
        return {
            values: {},
            onChange: null
        };
    },

    getInitialState: function() {
        return {
            loaded   : false,
            products : []
        };
    },

    render: function() {
        var Products = this.state.products.map(function(product, index) {
            return React.createElement(Radix.Components.get('FormProductEmail'), {
                key         : product._id,
                productId   : product._id,
                productKey  : product.key,
                productName : product.name,
                description : product.description,
                onChange    : this.props.onChange,
                optedIn     : this._isOptedIn(product._id)
            });
        }.bind(this));
        return (React.createElement('div', { className: 'form-products-email' }, Products));
    },

    _isOptedIn: function(productId) {
        var key = 'submission:optIns.' + productId;
        return (this.props.values.hasOwnProperty(key) && 'true' === this.props.values[key]);
    }
});
