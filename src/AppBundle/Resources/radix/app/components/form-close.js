import Ember from 'ember';

export default Ember.Component.extend({
    tagName: 'button',
    classNames: ['close'],
    label: 'Close',
    transitionTo: null,

    attributeBindings: ['label:aria-label', 'tagName:type'],

    routing: Ember.inject.service('-routing'),

    click: function() {
        this._redirectToRoute();
    },

    _redirectToRoute: function() {
        let routeName  = this.get('transitionTo');
        if (!routeName) {
            return;
        }
        this.get('routing').transitionTo(routeName);
    }

});
