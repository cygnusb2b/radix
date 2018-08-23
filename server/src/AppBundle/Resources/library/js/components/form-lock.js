React.createClass({ displayName: 'ComponentFormLock',

    getDefaultProps: function() {
        return {
            ref : null,
        };
    },

    getInitialState: function() {
        return {
            locked : false
        };
    },

    lock: function() {
        this.setState({ locked: true })
    },

    render: function() {
        var element = React.createElement('div');
        if (this.state.locked) {
            element = React.createElement('div', { ref: this.props.ref, className: 'form-lock' }, React.createElement('i', {className: 'pcfa pcfa-spinner pcfa-5x pcfa-pulse'}));
        }
        return (element)
    },

    unlock: function() {
        this.setState({ locked: false })
    },
});
