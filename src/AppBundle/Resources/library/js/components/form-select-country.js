React.createClass({ displayName: 'ComponentFormSelectCountry',

    getDefaultProps: function() {
        return {
            name     : 'countryCode',
            label    : 'Country',
            selected : null
        };
    },

    getInitialState: function() {
        return {
            loaded: false,
            options: []
        };
    },

    componentDidMount: function() {
        Ajax.send('/app/util/country-options', 'GET').then(
            function(response) {
                this.setState({ loaded: true, options: response.data });
            }.bind(this)
        );
    },

    render: function() {
        var props = {
            name  : this.props.name,
            label : this.props.label
        };
        if (this.state.loaded) {
            props.selected = this.props.selected;
            props.options  = this.state.options;
        }
        return (
            React.createElement(Radix.Forms.getComponent('Select'), props)
        )
    }
});
