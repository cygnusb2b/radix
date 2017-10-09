import Ember from 'ember';
import Button from '../form-button';

const { computed } = Ember;

export default Button.extend({

    model: null,
    displayWarning: false,
    field: null,

    label: null,
    icon: 'ion-close-round',

    confirmValue: null,
    transitionTo: null,

    classNames:[],

    layoutName: 'components/form-button',

    routing: Ember.inject.service('-routing'),
    loading: Ember.inject.service('loading'),
    confirm: Ember.inject.service(),

    click: function() {
        let _this        = this;
        let model        = this.get('model');
        let confirmValue = this.get('confirmValue');
        let field        = this.get('field');

        if (true === _this.get('displayWarning')) {
            this.get('confirm').delete(model, confirmValue, function() {
                _this.get('loading').show();
                    model.toggleProperty(field);
                    model.save().then(function() {
                        _this.get('loading').hide();
                        _this._redirectToRoute();
                }, function() {
                    // @todo Handle errors globally??
                    _this.get('loading').hide();
                });
            });
        }
        else {
            model.toggleProperty(field);
            model.save();
        }
    },

    _redirectToRoute: function() {
        let routeName  = this.get('transitionTo');
        if (!routeName) {
            return;
        }
        this.get('routing').transitionTo(routeName);
    }
});
