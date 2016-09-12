import Ember from 'ember';
import Button from '../form-button';

export default Button.extend({

    model: null,

    confirmValue: null,
    rollbackRels: false,

    label: Ember.computed('model.isNew', function() {
        // @todo This should support a direct set as well, so this can be overriden from a template.
        return this.get('model.isNew') ? 'Discard' : 'Undo';
    }),

    icon: Ember.computed('model.isNew', function() {
        // @todo This should support a direct set as well, so this can be overriden from a template.
        return this.get('model.isNew') ? 'ion-close-round' : 'ion-ios-undo';
    }),

    transitionTo: null,
    with: Ember.computed('model.isNew', function() {
        // @todo This should support a direct set as well, so this can be overriden from a template.
        return this.get('model.isNew') ? true : false;
    }),

    layoutName: 'components/form-button',

    disabled: Ember.computed('model.hasDirtyAttributes', 'model.isSaving', function() {
        // @todo This should support a direct set as well, so this can be overriden from a template.
        return this.get('model.isSaving') || !this.get('model.hasDirtyAttributes');
    }),

    routing: Ember.inject.service('-routing'),
    confirm: Ember.inject.service(),
    utility: Ember.inject.service('model-utility'),

    click: function() {
        let _this        = this;
        let model        = this.get('model');
        let confirmValue = this.get('confirmValue');

        this.get('confirm').unsaved(model, confirmValue, null, _this.get('rollbackRels'), function() {
            _this.get('utility').rollback(model, _this.get('rollbackRels'));
            _this._redirectToRoute();
        });
    },

    didReceiveAttrs: function() {
        let className = this.get('model.isNew') ? 'btn-danger' : 'btn-warning';
        this.get('classNames').push(className);
    },

    _redirectToRoute: function() {
        let routeName  = this.get('transitionTo');
        let params     = this.get('withModel') ? [this.get('model.id')] : undefined;
        if (!routeName) {
            return;
        }
        this.get('routing').transitionTo(routeName, params);
    },
});
