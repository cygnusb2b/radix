React.createClass({ displayName: 'ComponentRecaptcha',

    _readyCheck : null,

    getResponse: function() {
        return (this.state.ready) ? grecaptcha.getResponse(this.state.widgetId) : '';
    },

    componentDidMount: function() {
        if (!this.state.ready) {
            this._readyCheck = setInterval(this._updateReadyState, 200);
        } else {
            var widgetId = grecaptcha.render(this.props.elementId, {
                sitekey            : this.props.sitekey,
                callback           : this._verifyCallback,
                type               : this.props.type,
                theme              : this.props.theme,
                size               : this.props.size,
                tabindex           : this.props.tabindex,
            });
            this.setState({ widgetId: widgetId });
        }
    },

    componentWillUnmount: function() {
        this.reset();
        clearInterval(this._readyCheck);
    },

    getDefaultProps: function() {
        return {
            elementId : 'g-recaptcha-radix',
            sitekey   : '6LcUfhAUAAAAAPB5BpkPBzTGeAPhobLZusL1Y78W',
            type      : 'image',
            theme     : 'light',
            size      : 'normal',
            tabindex  : 0,
        }
    },

    getInitialState: function() {
        return {
            ready: this._isReady(),
            widgetId: null,
        }
    },

    render: function() {
        Debugger.log('ComponentRecaptcha', 'render()', this);
        return (
            React.createElement('div', {
                id : this.props.elementId,
                className : 'form-element-wrapper',
            })
        )
    },

    reset: function() {
        if (this.state.ready) {
            grecaptcha.reset(this.state.widgetId);
        }
    },

    _isReady: function() {
        return 'undefined' !== typeof window && 'undefined' !== typeof window.grecaptcha;
    },

    _updateReadyState: function() {
        if (this._isReady()) {
            this.setState({ ready : true });
            clearInterval(this._readyCheck);
        }
    }
});
