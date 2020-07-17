import Ember from 'ember';

const { inject: { service }, Route } = Ember;

export default Route.extend({

    query: service('model-query'),

    model: function() {
        return this.get('query').execute('core-application');
    },

    actions: {
        loadTabs: function() {
            return [
                { key : 'general',      text : 'General',      icon : 'ion-document',            template : 'core/applications/-general', active : true },
                { key : 'settings',     text : 'Settings',     icon : 'ion-gear-a',                 template : 'core/applications/-settings' },
                { key : 'posts',        text : 'Comments',     icon : 'ion-chatbubbles',                 template : 'core/applications/-posts' },
                { key : 'users',        text : 'Users',        icon : 'ion-person-stalker',               template : 'core/applications/-users' },
                { key : 'info',         text : 'Info',         icon : 'ion-information-circled', template : 'core/applications/-info' },
            ];
        },
    }

});
