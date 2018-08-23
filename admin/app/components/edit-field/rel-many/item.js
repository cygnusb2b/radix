import Ember from 'ember';

export default Ember.Component.extend({
    model: {},

    confirmField: null,
    confirmValue: Ember.computed('confirmField', function() {
        let field = this.get('confirmField');
        if (!field) {
            return;
        }
        return this.get('model').get(field);
    }),

    editTemplate: Ember.computed(function() {
        return 'components/edit-field/rel-many/item/' + this.get('model').constructor.modelName + '-edit';
    }),
    displayTemplate: Ember.computed(function() {
        return 'components/edit-field/rel-many/item/' + this.get('model').constructor.modelName + '-display';
    }),

    isSortable: false,
    editing: false,

    confirm: Ember.inject.service(),
    utility: Ember.inject.service('model-utility'),

    showEditor: Ember.computed('editing', 'model.isNew', function() {
        return (this.get('model.isNew') || this.get('editing'));
    }),

    actions: {
        edit: function() {
            this.set('editing', true);
        },
        close: function() {
            let _this        = this;
            let model        = this.get('model');

            this.get('confirm').unsaved(model, this.get('confirmValue'), null, false, function() {
                _this.set('editing', false);
                _this.get('utility').rollback(model, false);
            }, function() {
                _this.set('editing', false);
            });
        }
    }
});
