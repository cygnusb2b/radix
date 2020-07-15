import Ember from 'ember';

export default Ember.Route.extend({

    templateName: 'product/email-deployments/edit',

    model: function() {
        return this.store.createRecord('product-email-deployment');
    }
});
