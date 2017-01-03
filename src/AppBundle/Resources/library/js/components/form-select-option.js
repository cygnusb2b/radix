React.createClass({ displayName: 'ComponentFormSelectOption',

    componentDidMount: function() {
        if (!this.props.value) {
            // "Hack" to fix issue with early versions of React not supporting an empty text value.
            // This is needed to ensure HTML5 required select functionality works as expected.
            this.getDOMNode().setAttribute('value', '');
        }
    },

    getDefaultProps: function() {
        return {
            value: "",
            label: null,
        };
    },

    render: function() {
        return (
            React.createElement('option', {
                value: this.props.value,
                label: this.props.label
            }, this.props.label)
        )
    }
});
