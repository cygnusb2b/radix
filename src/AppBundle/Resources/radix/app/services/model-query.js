import Ember from 'ember';

const { Service, typeOf, inject: { service } } = Ember;

export default Service.extend({

    store   : service(),
    loading : service(),

    execute : function(modelType, criteria, limit, offset, sort) {
        if (!modelType) {
            throw new Error('No model type specified. Unable to perfrom query');
        }

        criteria = ('object' === typeOf(criteria)) ? criteria : {};
        limit    = parseInt(limit)  || 25;
        offset   = parseInt(offset) || 0;
        sort     = sort || "-createdDate";

        let loading = this.get('loading');
        loading.show();

        let promise = this.get('store').query(modelType, {
            page   : { offset : offset, limit : limit },
            filter : { query : { criteria: JSON.stringify(criteria) } },
            sort   : sort,
        });
        promise.then((results) => { return results; });
        promise.finally(() => loading.hide());
        return promise;
    },
});
