import Ember from 'ember';

export default Ember.Component.extend({

    relField: null,

    header: null,

    listTemplate: 'components/edit-field/rel-many/list',
    detailsTemplate: null,

    model: {},
    items: [],
    sorted: Ember.computed('items.@each.sequence', function() {
        return this.get('items').sortBy('sequence');
    }),
    recompute: false,

    enableSort: false,
    isSortable: Ember.computed('enableSort', 'model.isNew', function() {
        if (this.get('model.isNew')) {
            return false;
        }
        return this.get('enableSort');
    }),


    sortOptions: null,
    hasSortOptions: Ember.computed('sortOptions', function() {
        return Ember.isArray(this.get('sortOptions')) && this.get('sortOptions.length');
    }),

    hasSorted: Ember.computed('sorted.@each.sequence', 'recompute', function() {
        let _this  = this;
        let sorted = false;

        this.get('sorted').forEach(function(item) {
            if (item.get('isNew')) {
                return;
            }
            if (_this.get('utility').isAttributeDirty(item, 'sequence')) {
                sorted = true;
                return;
            }
        });
        return sorted;
    }),

    changedModels:  Ember.computed('sorted.@each.sequence', function() {
        let _this  = this;
        let sorted = [];
        this.get('sorted').forEach(function(item) {
            if (_this.get('utility').isAttributeDirty(item, 'sequence')) {
                sorted.pushObject(item);
            }
        });
        return sorted;
    }),

    utility: Ember.inject.service('model-utility'),
    loading: Ember.inject.service('loading'),

    didReceiveAttrs: function() {
        let options   = this.get('sortOptions');
        let formatted = [];
        if ('string' === Ember.typeOf(options)) {
            let split = options.split(',');
            for (var i = 0; i < split.length; i++) {
                formatted.pushObject({
                    key:   split[i],
                    label: Ember.String.capitalize(split[i]),
                });
            }
        }
        this.set('sortOptions', formatted);
    },

    actions: {
        reorderItems: function(ordered) {
            for (var i = 0; i < ordered.length; i++) {
                let model = ordered[i];
                model.set('sequence', i);
            }
        },
        sortBy: function(key) {
            let items    = this.get('sorted');
            let sequence = 0;

            let sorted = items.sortBy(key);
            sorted.forEach(function(choice) {
                choice.set('sequence', sequence);
                sequence++;
            });
        },
        applySort: function() {
            let _this     = this;
            let changed   = this.get('changedModels');
            let total     = changed.get('length');
            let completed = 0;
            let error     = false;

            this.get('loading').show();

            changed.forEach(function(model) {
                if (true === error) {
                    // Break the save loop.
                    return;
                }
                model.save().then(function() {
                    completed++;
                    if (completed === total) {
                        _this.get('loading').hide();
                        _this.toggleProperty('recompute');
                    }
                }, function() {
                    // @todo Handle errors globally?
                    error = true;
                    _this.get('loading').hide();
                });
            });
        },
        discardSort: function() {
            // @todo Confirm?
            let _this = this;
            this.get('changedModels').forEach(function(model) {
                _this.get('utility').rollbackAttribute(model, 'sequence');
            });
        },
        setSequence: function(properties) {
            if (!this.get('isSortable')) {
                return;
            }
            let last = this.get('sorted.lastObject');
            if (last) {
                properties.sequence = parseInt(last.get('sequence')) + 1;
            }
        }
    },
});
