import Ember from 'ember';

export default Ember.Route.extend({

    confirm: Ember.inject.service(),
    utility: Ember.inject.service('model-utility'),

    model: function(params) {
        return this.store.findRecord('product-tag', params.id);
    },
});
