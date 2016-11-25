import Ember from 'ember';

const { inject: { service }, Route } = Ember;

export default Route.extend({

    query   : service('model-query'),

    model: function() {
        return this.get('query').execute('product-tag');
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
