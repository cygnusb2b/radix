import Ember from 'ember';

export default Ember.Component.extend({
    confirm: Ember.inject.service(),
    value: Ember.computed('confirm.confirmValue', function() {
        return this.get('confirm.confirmValue');
    }),

    actions: {
        confirmed: function() {
            let callback   = this.get('confirm.onDeleteConfirm');
            if ('function' === Ember.typeOf(callback)) {
                callback();
            }
        }
    }
});
