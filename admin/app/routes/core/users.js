import Ember from 'ember';

const { inject: { service }, Route } = Ember;

export default Route.extend({

    query: service('model-query'),

    model: function() {
        return this.get('query').execute('core-user');
    },

    actions: {
        loadTabs: function() {
            return [
                { key : 'general',      text : 'General',      icon : 'ion-document',            template : 'core/users/-general', active : true },
                { key : 'credentials',  text : 'Credentials',  icon : 'ion-locked',              template : 'core/users/-credentials'         },
                { key : 'info',         text : 'Info',         icon : 'ion-information-circled', template : 'core/users/-info'         },
            ];
        }
    }

});
