import ApplicationRoute from 'modlr/routes/application';

export default ApplicationRoute.extend({
    beforeModel: function() {
        this.set('type', 'model');
        this._super();
    },
    model: function(params) {
        return this.store.findRecord('model', params.id);
    },
    renderTemplate: function() {
        this.render();
        this.render('model.edit', {
            into: 'application',
            outlet: 'edit'
        });
    }
});
