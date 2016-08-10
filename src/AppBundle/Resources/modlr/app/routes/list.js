import ApplicationRoute from 'modlr/routes/application';
import ModelCrud from 'modlr/mixins/model-crud';

export default ApplicationRoute.extend(ModelCrud, {
    itemTemplate: null,

    limit:  25,
    offset: 0,

    model: function() {
        let _this = this;
        let criteria = {};

        this.showLoading();

        return this.store.query(this.get('type'), {
            page: {
                offset: parseInt(this.get('offset')),
                limit:  parseInt(this.get('limit'))
            },
            filter: {
                query: {
                    criteria: JSON.stringify(criteria)
                }
            },
            sort: "-createdDate,name",
        }).then(function(results) {
            _this.hideLoading()
            return results;
        }, function() {
            _this.hideLoading()
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
    },
    actions: {
        recordAdded: function() {
            this.refresh();
        }
    }
});
