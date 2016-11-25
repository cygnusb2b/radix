import Ember from 'ember';

const { computed, typeOf } = Ember;

export default Ember.Component.extend({
    items : computed(function() {
        let loader = this.get('navItemLoader');
        return ('function' === typeOf(loader)) ? loader() : [];
    }),
    tagName    : 'div',
    classNames : ['list-group']
});
