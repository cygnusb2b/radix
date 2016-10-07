React.createClass({ displayName: 'ComponentModal',

    getDefaultProps: function() {
        return {
            ref: null
        };
    },

    getInitialState: function() {
        return {
            contents: null
        };
    },

    show: function() {
        $('body').addClass('show-modal');
        this._getObject().show();
    },

    hide: function() {
        $('body').removeClass('show-modal');
        this._getObject().hide();
    },

    toggle: function() {
        var obj = this._getObject();
        if (obj.is(':hidden')) {
            $('body').addClass('show-modal');
        } else {
            $('body').removeClass('show-modal');
        }
        obj.toggle();
    },

    render: function() {
        return (
            React.createElement('div', { className: 'login page wrap' },
                React.createElement('button', { onClick: this.hide, className: 'dismiss' }, 'Close'),
                this.state.contents
            )
        );
    },

    _getObject: function() {
        return $('#radix-modal');
    }
});
