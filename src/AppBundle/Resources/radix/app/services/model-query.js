import Ember from 'ember';

const { Service, typeOf, inject: { service } } = Ember;

export default Service.extend({

    store   : service(),
    loading : service(),

    buildParams : function(criteria, limit, offset, sort) {
        criteria = ('object' === typeOf(criteria)) ? criteria : {};

        if (0 !== limit) {
            limit = parseInt(limit)  || 25;
        }
        offset   = parseInt(offset) || 0;
        sort     = sort || "-createdDate";

        let params = {
            filter : { query : { criteria: JSON.stringify(criteria) } },
            sort   : sort
        };
        if (limit > 0) {
            params.page = {
                limit  : limit,
                offset : offset,
            };
        }
        return params;
    },

    execute : function(modelType, criteria, limit, offset, sort) {
        if (!modelType) {
            throw new Error('No model type specified. Unable to perfrom query');
        }

        let loading = this.get('loading');
        loading.show();

        let params  = this.buildParams(criteria, limit, offset, sort);
        let promise = this.get('store').query(modelType, params);

        promise.then((results) => { return results; });
        promise.finally(() => loading.hide());
        return promise;
    },
});
