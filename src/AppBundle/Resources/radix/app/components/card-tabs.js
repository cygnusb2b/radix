import Ember from 'ember';

const { computed } = Ember;

export default Ember.Component.extend({
    model     : null,
    tabs      : computed('model', function() {
        return this.get('tabLoader')();
    }),

    routeName : null,

    editRouteName : computed('routeName', function() {
        return `${this.get('routeName')}.edit`;
    }),

    tagName    : 'div',
    classNames : ['card']
});
