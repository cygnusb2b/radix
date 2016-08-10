import DS from 'ember-data';
import Ember from 'ember';

export default DS.JSONAPISerializer.extend({
    keyForAttribute: function(attr) {
        return Ember.String.camelize(attr);
    },
    keyForLink: function(attr) {
        return Ember.String.camelize(attr);
    },
    keyForRelationship: function(attr) {
        return Ember.String.camelize(attr);
    },

    payloadKeyFromModelName: function(modelName) {
        return modelName;
    },
    normalizeArrayResponse: function(...args) {
        const normalized = this._super(...args);
        if (normalized.meta == null) {
            normalized.meta = {};
        }

        normalized.meta.links = normalized.links;
        return normalized;
    },
    extractMeta: function(store, typeClass, payload) {
        if (payload && payload.hasOwnProperty('links')) {
            let meta = payload.links;
            delete payload.links;
            return meta;
        }
    },
    normalizeQueryResponse: function(store, clazz, payload) {
        const result = this._super(...arguments);
        result.meta = result.meta || {};

        if (payload.links) {
            result.meta.pagination = this.createPageMeta(payload.links);
        }

        return result;
    },
    createPageMeta: function(data) {
        let meta = {};
        Object.keys(data).forEach(function(type) {
            const link = data[type];
            meta[type] = {};
            let a = document.createElement('a');
            a.href = link;

            a.search.slice(1).split('&').forEach(function(pairs) {
                const [param, value] = pairs.split('=');

                if (param === 'page%5Boffset%5D') {
                    meta[type].offset = parseInt(value);
                }
                if (param === 'page%5Blimit%5D') {
                    meta[type].limit = parseInt(value);
                }
            });
            a = null;
        });

        return meta;
    }
});
