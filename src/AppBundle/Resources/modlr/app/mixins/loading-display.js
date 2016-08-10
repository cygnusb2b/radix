import Ember from 'ember';

export default Ember.Mixin.create({

    loadingClassName: 'show-loading',

    showLoading: function(className) {
        Ember.$('body').addClass(this._getLoadingClassName(className));
    },

    hideLoading: function(className) {
        Ember.$('body').removeClass(this._getLoadingClassName(className));
    },

    _getLoadingClassName: function(className) {
        return className || this.get('loadingClassName');
    }
});
