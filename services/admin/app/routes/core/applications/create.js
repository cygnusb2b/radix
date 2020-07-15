import Ember from 'ember';

export default Ember.Route.extend({

    confirm: Ember.inject.service(),

    templateName: 'core/applications/edit',

    model: function() {
        return this.store.createRecord('core-application');
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
        },
        addAllowedOrigin(value) {
            this.controllerFor('core.applications.create')
                .get('model.allowedOrigins')
                .pushObject(value);
        }
    }
});
