import Ember from 'ember';

export default Ember.Route.extend({

    confirm: Ember.inject.service(),
    utility: Ember.inject.service('model-utility'),

    model: function(params) {
        return this.store.findRecord('question', params.id);
    },

    actions: {
        willTransition: function(transition) {
            // let _this = this;
            // let model = this.controller.get('model');

            // this.get('confirm').unsaved(model, model.get('name'), transition, true, function() {
            //     _this.get('utility').rollback(model, true);
            // });
        }
    }
});
