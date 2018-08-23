import Ember from 'ember';

const { computed, typeOf } = Ember;

export default Ember.Component.extend({
    items    : [],
    getItems : computed(function() {
        let loader = this.get('navItemLoader');
        return ('function' === typeOf(loader)) ? loader() : this.get('items');
    }),
    tagName    : 'div',
    classNames : ['list-group', 'm-b-1']
});
