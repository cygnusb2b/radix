import Ember from 'ember';

export default Ember.Route.extend({

    loading: Ember.inject.service(),

    limit:  25,
    offset: 0,

    model: function() {
        let _this = this;
        let criteria = {};

        this.get('loading').toggle();

        return this.store.query('demographic', {
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
            _this.get('loading').toggle();
            return results;
        }, function() {
            _this.get('loading').toggle();
        });
    },

    setupController: function(controller, model) {
        this._super(controller, model);
    },

    actions: {
        recordAdded: function() {
            this.refresh();
        }
    }

});
