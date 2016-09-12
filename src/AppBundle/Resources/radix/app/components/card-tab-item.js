import Ember from 'ember';

export default Ember.Component.extend({
    icon: null,
    text: null,
    active: false,
    disabled: false,
    target: null,
    classNames: ['nav-item', 'card-tab-item'],
    attributeBindings: ['target:data-card-tab-target'],
    tagName: 'li',

    didInsertElement: function() {
        if (true === this.get('active')) {
            this._setActive();
        }
    },

    _clearActive: function() {
        Ember.$('.card-tab-item .active').removeClass('active');
        Ember.$('.card-tab-block').hide();
    },

    _setActive: function() {
        let target = this.get('target');
        Ember.$('.card-tab-block[data-card-tab-key="'+target+'"]').show();
        this.$('a').addClass('active');
    },

    actions: {
        toggle: function() {
            this._clearActive();
            this._setActive();
        }
    }

});
