import Ember from 'ember';

const { inject: { service }, Route } = Ember;

export default Route.extend({

    confirm: service(),
    utility: service('model-utility'),

    model: function(params) {
        return this.store.findRecord('identity-internal', params.id);
    },

    actions: {
        willTransition: function(transition) {
            let _this = this;
            let model = this.controller.get('model');

            this.get('confirm').unsaved(model, model.get('fullName'), transition, true, function() {
                _this.get('utility').rollback(model, true);
            });
        }
    }
});
