import Ember from 'ember';

export default Ember.Route.extend({

    confirm: Ember.inject.service(),

    templateName: 'form/definitions/edit',

    model: function() {
        return this.store.createRecord('form-definition');
    },

    actions: {
        willTransition: function(transition) {
            // let model = this.controller.get('model');
            // this.get('confirm').unsaved(model, model.get('name'), transition, false, function() {
            //     model.rollbackAttributes();
            // });

            // if (!model.get('hasDirtyAttributes')) {
            //     this.send('recordAdded');
            // }
        }
    }
});
