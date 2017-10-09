import Ember from 'ember';
import Button from '../form-button';

export default Button.extend({

    model: null,

    label: 'Delete',
    icon: 'ion-close-round',

    confirmValue: null,
    transitionTo: null,

    classNames: ['btn-danger'],

    layoutName: 'components/form-button',

    routing: Ember.inject.service('-routing'),
    loading: Ember.inject.service('loading'),
    confirm: Ember.inject.service(),

    click: function() {
        let _this        = this;
        let model        = this.get('model');
        let confirmValue = this.get('confirmValue');

        this.get('confirm').delete(model, confirmValue, function() {
            _this.get('loading').show();
                model.set('deleted', true);
                model.save().then(function() {
                    _this.get('loading').hide();
                    _this._redirectToRoute();
            }, function() {
                // @todo Handle errors globally??
                _this.get('loading').hide();
            });
        });
    },

    _redirectToRoute: function() {
        let routeName  = this.get('transitionTo');
        if (!routeName) {
            return;
        }
        this.get('routing').transitionTo(routeName);
    }
});
