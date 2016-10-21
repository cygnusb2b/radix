React.createClass({ displayName: 'ComponentFormSelectOption',

    getDefaultProps: function() {
        return {
            value: null,
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
