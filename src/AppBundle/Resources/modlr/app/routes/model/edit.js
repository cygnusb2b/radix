import ApplicationRoute from 'modlr/routes/application';

export default ApplicationRoute.extend({

    model: function(params) {
        return this.store.findRecord('model', params.id);
    },

    setupController: function(controller, model) {
        this._super(controller, model);
        controller.set('model', model);
    },

    actions: {
        willTransition: function(transition) {
            let model = this.controller.get('model') || {};
            if (model.get('hasDirtyAttributes') && !confirm('All changes will be discarded. Are you sure you want to continue?')) {
                transition.abort();
            } else {
                model.rollbackAttributes();
                return true;
            }
        }
    }
});
