React.createClass({ displayName: 'ComponentParseQueryString',

    getDefaultProps: function() {
        return {
            query: window.location.search
        };
    },

    getInitialState: function() {
        return {};
    },

    render: function() {
        var flat   = Utils.parseQueryString(this.props.query);
        var scoped = Utils.parseQueryString(this.props.query, true);
        return (
            React.createElement('div', null,
                React.createElement('p', null, 'Enter a query string into the browser a la ', React.createElement('code', null , '?radix.action=verify-email&radix.key=value&foo=bar'), ' to parse.'),
                React.createElement('p', {className: 'card-text'}, 'Query string parsed as flat object.'),
                React.createElement('div', { className: 'alert alert-info', role: 'alert' },
                    React.createElement('pre', null, React.createElement('small', null, React.createElement('samp', null,
                        JSON.stringify(flat, null, '\t')
                    )))
                ),
                React.createElement('p', {className: 'card-text'}, 'Query string parsed as a Radix scoped object.'),
                React.createElement('div', { className: 'alert alert-info', role: 'alert' },
                    React.createElement('pre', null, React.createElement('small', null, React.createElement('samp', null,
                        JSON.stringify(scoped, null, '\t')
                    )))
                )
            )
        )
    }
});
