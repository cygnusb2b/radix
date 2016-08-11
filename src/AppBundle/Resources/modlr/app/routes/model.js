import Ember from 'ember';
import ModelCrud from 'modlr/mixins/model-crud';

export default Ember.Route.extend(ModelCrud, {

    limit:  25,
    offset: 0,

    model: function() {
        let _this = this;
        let criteria = {};

        this.showLoading();

        return this.store.query('model', {
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
            _this.hideLoading();
            return results;
        }, function() {
            _this.hideLoading();
        });
    },

    // renderTemplate: function(controller, model) {
    //     this.render();
    //     this.render('placeholder', {
    //         into: 'model'
    //     });
    // },

    actions: {
        recordAdded: function() {
            this.refresh();
        }
    }
});
