import ApplicationRoute from 'modlr/routes/application';

export default ApplicationRoute.extend({
    itemTemplate: null,

    model: function(params) {
        let criteria = {};

        console.info(this.get('type'));
        return {};

        return this.store.query(this.get('type'), {
            page: {
                offset: parseInt(params.offset),
                limit:  parseInt(params.limit)
            },
            filter: {
                query: {
                    criteria: JSON.stringify(criteria)
                }
            },
            sort: "-created,name",
        }).then(function(results) {
            return results;
        });
    },
    renderTemplate: function(controller, model) {
        this._super(controller, model);

        controller.set('type', this.get('type'));
        controller.set('itemTemplate', this.get('type') + '/item');

        this.render();
        this.render('list', {
            into: 'application',
            outlet: 'list',
            controller: controller,
            model: model
        });
        this.render('placeholder', {
            into: 'application',
            outlet: 'edit'
        });
        this.render('list/_footer', {
            into: 'list',
            outlet: '_footer'
        });
    }
});
