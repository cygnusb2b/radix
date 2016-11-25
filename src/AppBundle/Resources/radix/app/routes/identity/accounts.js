import Ember from 'ember';

const { inject: { service }, Route } = Ember;

export default Route.extend({

    query: service('model-query'),

    model: function() {
        return this.get('query').execute('identity-account');
    },

    actions: {
        loadTabs: function() {
            return [
                { key : 'general',  text : 'General',  icon : 'ion-document',            template : 'identity/accounts/-general', active : true },
                { key : 'info',     text : 'Info',     icon : 'ion-information-circled', template : 'identity/accounts/-info'     },
            ];
        },
        recordAdded: function() {
            this.refresh();
        }
    }

});
