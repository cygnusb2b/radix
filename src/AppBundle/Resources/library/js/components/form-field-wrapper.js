React.createClass({ displayName: 'ComponentFormFieldWrapper',

    getDefaultProps: function() {
        return {
            name      : 'unknown',
            tagName   : 'div',
            className : 'form-element-wrapper'
        };
    },

    render: function() {
        var props = {
            className: this.props.className
        };
        if (this.props.name) {
            props.className = props.className + ' ' + this.props.name;
        }
        return (React.createElement(this.props.tagName, props, this.props.children))
    }
});
