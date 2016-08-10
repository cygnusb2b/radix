import ApplicationRoute from 'modlr/routes/application';

export default ApplicationRoute.extend({
    beforeModel: function() {
        this.set('type', 'mixin');
        this._super();
    },
    model: function(params) {
        return this.store.findRecord('mixin', params.id);
    },
    renderTemplate: function() {
        this.render();
        this.render('mixin.edit', {
            into: 'application',
            outlet: 'edit'
        });
    }
});
