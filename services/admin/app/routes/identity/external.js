import Ember from 'ember';

const { inject: { service }, Route } = Ember;

export default Route.extend({

    query: service('model-query'),

    model: function() {
        return this.get('query').execute('identity-external');
    },

    actions: {
        loadTabs: function() {
            return [
                { key : 'general',  text : 'General',  icon : 'ion-document',            template : 'identity/-general', active : true },
                { key : 'info',     text : 'Info',     icon : 'ion-information-circled', template : 'identity/-info'     },
            ];
        }
    }
});
