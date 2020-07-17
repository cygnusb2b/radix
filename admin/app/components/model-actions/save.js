import Ember from 'ember';
import Button from '../form-button';

export default Button.extend({

    model: null,

    label: 'Save',
    icon: 'ion-checkmark-round',

    transitionTo: null,
    withModel: true,

    classNames: ['btn-success'],

    layoutName: 'components/form-button',

    disabled: Ember.computed('model.hasDirtyAttributes', 'model.isSaving', function() {
        return this.get('model.isSaving') || !this.get('model.hasDirtyAttributes');
    }),

    routing: Ember.inject.service('-routing'),
    loading: Ember.inject.service('loading'),

    click: function() {
        this.get('loading').show();

        let _this = this;
        let model = this.get('model');

        // @todo Handle model validation here
        model.save().then(function() {
            _this.get('loading').hide();
            _this._redirectToRoute();
        }, function() {
            // @todo Handle errors globally??
            _this.get('loading').hide();
        });
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
