import Ember from 'ember';
import JSONAPIAdapter from 'ember-data/adapters/json-api';
import DataAdapterMixin from 'ember-simple-auth/mixins/data-adapter-mixin';
import { inject } from '@ember/service';
import Inflector from 'ember-inflector';

export default JSONAPIAdapter.extend(DataAdapterMixin, {
  queryService: inject('model-query'),

  coalesceFindRequests: true,
  namespace: '/api/1.0',
  authorize(xhr) {
    const token = this.get('session.data.authenticated.token');
    const key = this.get('session.data.application.key');
    if (token) xhr.setRequestHeader('Authorization', `Bearer ${token}`);
    if (key) xhr.setRequestHeader('X-Radix-AppId', key);
  },

  pathForType: function (type) {
    return Inflector.inflector.singularize(Ember.String.dasherize(type));
  },

  findMany: function (store, type, ids, snapshots) {
    let url = this.buildURL(type.modelName, ids, snapshots, 'findMany');
    let params = this.get('queryService').buildParams({
      id: { $in: ids }
    }, ids.length);
    return this.ajax(url, 'GET', { data: params });
  }
});
