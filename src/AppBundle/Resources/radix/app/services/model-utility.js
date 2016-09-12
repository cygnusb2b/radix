import Ember from 'ember';

export default Ember.Service.extend({

    extractRelationshipMeta: function(model, field) {
        let meta;
        model.eachRelationship(function(name, descriptor) {
            if (field === name) {
                meta = descriptor;
            }
        });
        return meta;
    },

    isAttributeDirty: function(model, attr) {
        let changed = model.changedAttributes();
        return (changed[attr]) ? true : false;
    },

    isDirty: function(model, withRels) {
        let dirty = model.get('hasDirtyAttributes');

        if (withRels) {
            if (dirty) {
                return true;
            }
            model.eachRelationship(function(name, descriptor) {
                if ('belongsTo' === descriptor.kind) {
                    if (model.get(name) && model.get(name).get('hasDirtyAttributes')) {
                        dirty = true;
                    }
                } else {
                    model.get(name).forEach(function(related) {
                        if (related && related.get('hasDirtyAttributes')) {
                            dirty = true;
                        }
                    });
                }
            });
        }
        return dirty;
    },

    rollback: function(model, withRels) {

        model.rollbackAttributes();

        if (withRels) {
            model.eachRelationship(function(name, descriptor) {
                if ('belongsTo' === descriptor.kind) {
                    let related = model.get(name);
                    if (related) {
                        related.get('content').rollbackAttributes();
                    }
                } else {
                    console.info('rollbackAttributes for ', name);
                    model.get(name).forEach(function(related) {
                        if (related) {
                            related.rollbackAttributes();
                        }
                    });
                }
            });
        }
    },

    rollbackAttribute: function(model, attr) {
        if (false === this.isAttributeDirty(model, attr)) {
            return model;
        }
        let old = model.changedAttributes()[attr][0];
        return model.set(attr, old);
    },
});
