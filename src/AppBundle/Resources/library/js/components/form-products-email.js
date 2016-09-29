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
            optIns  : [],
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
            // @todo, this should be a FormProductEmail item that includes the email deployment component.
            return React.createElement(Radix.Components.get('ProductEmailDeployment'), {
                key         : index,
                id          : product._id,
                productKey  : product.key,
                name        : product.name,
                description : product.description
            });
        });
        return (React.createElement('div', { className: 'form-products-email' }, Products));
    },
});
