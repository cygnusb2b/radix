import Ember from 'ember';

const { inject: { service }, Component } = Ember;

export default Component.extend({
    tagName: 'li',
    classNames: ['nav-item', 'dropdown'],
    session: service('session'),
    userManager: service(),
    actions: {
        logout: function() {
            this.get('session').invalidate();
        }
    }
});
