React.createClass({ displayName: 'ComponentFormFieldWrapper',

    getDefaultProps: function() {
        return {
            name      : 'unknown',
            tagName   : 'div',
            className : null,
        };
    },

    render: function() {
        var props = {
            className: 'form-element-wrapper'
        };
        if (this.props.className) {
            props.className = props.className + ' ' + this.props.className;
        }
        return (React.createElement(this.props.tagName, props, this.props.children))
    }
});
