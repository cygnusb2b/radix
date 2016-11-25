import Ember from 'ember';

const { inject: { service }, Route } = Ember;

export default Route.extend({

    query: service('model-query'),

    model: function() {
        return this.get('query').execute('question-choice');
    },

    actions: {
        loadTabs: function() {
            return [
                { key : 'general',  text : 'General',  icon : 'ion-document',            template : 'demographic/choices/-general', active : true },
                { key : 'info',     text : 'Info',     icon : 'ion-information-circled', template : 'demographic/choices/-info'     },
            ];
        },
        recordAdded: function() {
            this.refresh();
        }
    }

});
