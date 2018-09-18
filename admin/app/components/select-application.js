import Ember from 'ember';

const { inject: { service }, Component, computed } = Ember;

export default Component.extend({
    tagName: 'li',
    classNames: ['nav-item'],
    classNameBindings: ['canSelect:dropdown'],

    session: service('session'),

    apps: computed('session.data.authenticated.applications.[]', function() {
        return this.get('session.data.authenticated.applications') || [];
    }),

    canSelect: computed('apps', function() {
        return this.get('apps.length') > 1;
    }),

    toSelect: computed('apps', function() {
        let selectedId = this.get('session.data.application.id');
        return this.get('apps').filter(function(item) {
            return item.id !== selectedId;
        }).sortBy('name');
    }),

    actions: {
        changeApp: function(app) {
            this.get('session').set('data.application', app);
            this.sendAction('onAppChange');
            this.$('.dropdown-menu').removeClass('show');
        }
    }
});
