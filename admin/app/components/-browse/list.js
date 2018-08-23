import Ember from 'ember';

const { Component, computed } = Ember;

export default Component.extend({
    routeName    : null,
    createRoute  : computed('routeName', function() {
        return `${this.get('routeName')}.create`;
    }),
    editRoute    : computed('routeName', function() {
        return `${this.get('routeName')}.edit`;
    }),
    canCreate    : true,
    items        : [],
});
