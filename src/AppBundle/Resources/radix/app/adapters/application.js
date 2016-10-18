import Ember from 'ember';
import DS from 'ember-data';
import DataAdapterMixin from 'ember-simple-auth/mixins/data-adapter-mixin';

export default DS.JSONAPIAdapter.extend(DataAdapterMixin, {
    authorizer: 'authorizer:core',

    namespace: '/api/1.0',

    pathForType: function (type) {
        return Ember.Inflector.inflector.singularize(Ember.String.dasherize(type));
    }
});
