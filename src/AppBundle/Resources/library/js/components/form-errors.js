React.createClass({ displayName: 'ComponentFormErrors',

    clear: function() {
        this.setState({ message: null })
    },

    getDefaultProps: function() {
        return {
            ref : null,
        };
    },

    getInitialState: function() {
        return {
            message : null
        };
    },

    display: function(message) {
        this.setState({ message: message });
    },

    displayAjaxError: function(jqXHR) {
        if (jqXHR.errors && jqXHR.errors.length) {
            this.display(jqXHR.errors[0].detail);
        } else {
            this.display('An unknown internal error occured. Please try again.');
        }
    },

    getStatusCodeFrom: function(jqXHR) {
        if (jqXHR.errors && jqXHR.errors.length) {
            return jqXHR.errors[0].status;
        }
        return 500;
    },

    getMeta: function(jqXHR) {
        if (jqXHR.errors && jqXHR.errors.length) {
            return jqXHR.errors[0].meta;
        }
        return {};
    },

    render: function() {
        var element = React.createElement('div');
        if (this.state.message) {
            element = React.createElement('p', { className: 'alert-danger alert', role: 'alert' }, React.createElement('strong', null, 'Oh snap! '), this.state.message)
        }
        return (element)
    }
});
