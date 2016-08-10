import ApplicationRoute from 'modlr/routes/application';

export default ApplicationRoute.extend({
    beforeModel: function() {
        this.set('type', 'embed');
        this._super();
    },
    model: function(params) {
        return this.store.findRecord('embed', params.id);
    },
    renderTemplate: function() {
        this.render();
        this.render('embed.edit', {
            into: 'application',
            outlet: 'edit'
        });
    }
});
