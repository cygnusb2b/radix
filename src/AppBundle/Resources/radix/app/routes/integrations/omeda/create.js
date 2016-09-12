import Ember from 'ember';

export default Ember.Route.extend({

    confirm: Ember.inject.service(),

    templateName: 'integrations/omeda/edit',

    model: function() {
        return this.store.createRecord('integration-client-omeda');
    },

    actions: {
        willTransition: function(transition) {
            let model = this.controller.get('model');
            this.get('confirm').unsaved(model, model.get('name'), transition, false, function() {
                model.rollbackAttributes();
            });

            if (!model.get('hasDirtyAttributes')) {
                this.send('recordAdded');
            }
        }
    }
});
