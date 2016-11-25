import Ember from 'ember';

export default Ember.Route.extend({

    loading: Ember.inject.service(),

    limit:  25,
    offset: 0,

    model: function() {
        let _this = this;
        let criteria = {};

        this.get('loading').show();

        return this.store.query('product-tag', {
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
            _this.get('loading').hide();
            return results;
        }, function() {
            _this.get('loading').hide();
        });
    },

    actions: {
        loadTabs: function() {
            return [
                { key : 'general',  text : 'General',  icon : 'ion-document',            template : 'product/tags/-general', active : true },
                { key : 'products', text : 'Products', icon : 'ion-pricetag',            template : 'product/tags/-products' },
                { key : 'info',     text : 'Info',     icon : 'ion-information-circled', template : 'product/tags/-info'     },
            ];
        },
        recordAdded: function() {
            this.refresh();
        }
    }

});
