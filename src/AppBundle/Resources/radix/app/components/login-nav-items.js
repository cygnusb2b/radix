import Ember from 'ember';

const { inject: { service }, Component, computed } = Ember;

export default Component.extend({
    session: service('session'),

    applicationName: computed('session.data.selectedApp', function() {
        return this.get('session.data.selectedApp.name') || '(none selected)';
    }),

    actions: {
        logout: function() {
            this.get('session').invalidate();
        }
    }
});
