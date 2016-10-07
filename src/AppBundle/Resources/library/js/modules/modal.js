function ModalModule()
{
    this.modal = null;

    this.config = {};

    this.module = React.createClass({ displayName: 'ModalModule',

        render: function() {
            return (React.createElement(Radix.Components.get('Modal'), { ref: this._setReference }));
        },

        _setReference: function(modal) {
            if (modal) {
                Radix.ModalModule.modal = modal;
            }
        }
    });

    this.render = function() {
        $('body').append('<div id="radix-modal" class="platform-element" data-element="modal"></div>');

        React.render(
            React.createElement(this.module),
            $('#radix-modal')[0]
        );
    }
}
