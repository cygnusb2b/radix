import Ember from 'ember';

const { inject: { service }, Component } = Ember;

export default Component.extend({
    session: service('session'),
    userManager: service(),
    actions: {
        logout: function() {
            this.get('session').invalidate();
        }
    }
});
