React.createClass({ displayName: 'ComponentFormSelectCountry',

    componentDidMount: function() {
        Ajax.send('/app/util/country-options', 'GET').then(
            function(response) {
                this.setState({ loaded: true, options: response.data });
            }.bind(this),
            function(jqXhr) {
                Debugger.error('Unable to load the country options.');
            }.bind(this)
        );
    },

    getDefaultProps: function() {
        return {
            name     : 'customer:primaryAddress.countryCode',
            label    : 'Country',
            selected : null,
            onChange : null
        };
    },

    getInitialState: function() {
        return {
            loaded: false,
            options: []
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
        return React.createElement(Radix.Components.get('FormSelect'), {
            name     : this.props.name,
            label    : this.props.label,
            onChange : this.props.onChange,
            selected : this.props.selected,
            options  : this.state.options
        });
    }
});
