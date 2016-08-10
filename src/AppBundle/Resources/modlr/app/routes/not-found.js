import ApplicationRoute from 'modlr/routes/application';

export default ApplicationRoute.extend({
    renderTemplate: function(controller, model) {
        this._super(controller, model);

        this.render();
        this.render('not-found', {
            into: 'application',
            outlet: 'list',
        });
    }
});
