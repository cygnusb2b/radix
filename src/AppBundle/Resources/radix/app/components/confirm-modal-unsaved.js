import Ember from 'ember';

export default Ember.Component.extend({
    confirm: Ember.inject.service(),
    value: Ember.computed('confirm.confirmValue', function() {
        return this.get('confirm.confirmValue');
    }),

    actions: {
        confirmed: function() {
            let transition = this.get('confirm.transition');
            let callback   = this.get('confirm.onUnsavedConfirm');

            if ('function' === Ember.typeOf(callback)) {
                callback();
            }
            if (transition) {
                console.info('retry');
                transition.retry();
            }
        }
    }
});
