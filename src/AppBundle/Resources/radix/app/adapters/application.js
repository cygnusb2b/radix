import Ember            from 'ember';
import JSONAPIAdapter   from 'ember-data/adapters/json-api';
import isEnabled        from 'ember-data/-private/features';
import DataAdapterMixin from 'ember-simple-auth/mixins/data-adapter-mixin';

const { inject: { service } } = Ember;


export default JSONAPIAdapter.extend(DataAdapterMixin, {

    queryService: service('model-query'),

    authorizer: 'authorizer:core',

    coalesceFindRequests: true,

    namespace: '/api/1.0',

    pathForType: function (type) {
        return Ember.Inflector.inflector.singularize(Ember.String.dasherize(type));
    },

    findMany: function(store, type, ids, snapshots) {

        if (isEnabled('ds-improved-ajax')) {
            throw new Error('Overriding findMany must now be re-evaluted.');
        }

        let url    = this.buildURL(type.modelName, ids, snapshots, 'findMany');
        let params = this.get('queryService').buildParams({
            id : { $in: ids }
        }, ids.length);
        return this.ajax(url, 'GET', { data: params });
    }
});
