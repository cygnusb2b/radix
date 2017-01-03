import Ember from 'ember';

const { Component } = Ember;

export default Component.extend({
    model: null,
    key: null,
    template: null,
    active: false,

    attributeBindings: ['key:data-card-tab-key'],
    classNames: ['card-tab-block'],
    tagName: 'div',

    didInsertElement: function() {
        let key = this.get('key');
        if (!Ember.$('[data-card-tab-target="'+key+'"] .card-tab-item').hasClass('active')) {
            this.$().hide();
        }
    },
});
