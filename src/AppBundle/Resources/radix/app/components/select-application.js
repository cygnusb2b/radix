import Ember from 'ember';

const { inject: { service }, Component, computed } = Ember;

export default Component.extend({
    session: service('session'),

    selectedApp: computed('session.data.selectedApp', {
        get(key) {
            return this.get('session.data.selectedApp');
        },
        set(key, value) {
            this.get('session').set('data.selectedApp', value);
            return value;
        }
    }),

    apps: computed('session.data.authenticated.applications', function() {
        let available = this.get('session.data.authenticated.applications') || [];
        return available.sortBy('fullName');
    }),

    actions: {
        selectApp: function(app) {
            this.set('selectedApp', app);
        }
    }
});
