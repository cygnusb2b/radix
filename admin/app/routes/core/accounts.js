import Ember from 'ember';

const { inject: { service }, Route } = Ember;

export default Route.extend({

    query: service('model-query'),

    model: function() {
        return this.get('query').execute('core-account');
    },

    actions: {
        loadTabs: function() {
            return [
                { key : 'general',  text : 'General',  icon : 'ion-document',            template : 'core/accounts/-general', active : true },
                { key : 'info',     text : 'Info',     icon : 'ion-information-circled', template : 'core/accounts/-info'     },
            ];
        }
    }

});
