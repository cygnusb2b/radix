import Ember from 'ember';

const { computed, typeOf } = Ember;

export default Ember.Component.extend({
    navs : computed(function() {
        let loader = this.get('navLoader');
        return ('function' === typeOf(loader)) ? loader() : [];
    }),
});
