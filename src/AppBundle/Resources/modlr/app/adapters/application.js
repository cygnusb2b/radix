import Ember from 'ember';
import DS from 'ember-data';

export default DS.JSONAPIAdapter.extend({
    namespace: '/api/1.0',

    pathForType: function (type) {
        return Ember.Inflector.inflector.singularize(Ember.String.dasherize(type));
    }
});
