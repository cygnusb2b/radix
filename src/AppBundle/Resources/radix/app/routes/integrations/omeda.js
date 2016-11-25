import Ember from 'ember';

const { inject: { service }, Route } = Ember;

export default Route.extend({

    query : service('model-query'),

    model : function() {
        return this.get('query').execute('integration-service-omeda');
    },

    actions : {
        loadTabs: function() {
            return [
                { key : 'settings', text : 'Settings', icon : 'ion-gear-a',              template : 'integrations/omeda/-settings', active : true },
                { key : 'info',     text : 'Info',     icon : 'ion-information-circled', template : 'integrations/omeda/-info' },
            ];
        },
    }
});
