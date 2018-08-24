import Ember from 'ember';

export default Ember.Route.extend({

    confirm: Ember.inject.service(),

    templateName: 'product/tags/edit',

    model: function() {
        return this.store.createRecord('product-tag');
    }
});
