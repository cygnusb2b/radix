import Ember from 'ember';

export default Ember.Service.extend({
    selector: '.loading-overlay',

    show: function() {
        this._getElement().show();
    },

    hide: function() {
        this._getElement().hide();
    },

    toggle: function() {
        this._getElement().toggle();
    },

    _getElement: function() {
        return Ember.$(this.get('selector'));
    }

});
