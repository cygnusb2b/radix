React.createClass({ displayName: 'ComponentFormLabel',

    getDefaultProps: function() {
        return {
            id: null,
            value: null,
            className: 'form-element-label'
        };
    },

    render: function() {
        var props = {
            htmlFor   : this.props.id,
            className : this.props.className
        };

        return (React.createElement('label', props, this.props.value))
    }
});
