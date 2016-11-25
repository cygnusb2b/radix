import Ember from 'ember';

const { inject: { service }, Route } = Ember;

export default Route.extend({

    query: service('model-query'),

    model: function() {
        return this.get('query').execute('question-tag');
    },

    actions: {
        loadTabs: function() {
            return [
                { key : 'general',      text : 'General',      icon : 'ion-document',            template : 'demographic/labels/-general', active : true },
                { key : 'demographics', text : 'Demographics', icon : 'ion-pricetag',            template : 'demographic/labels/-demographics' },
                { key : 'info',         text : 'Info',         icon : 'ion-information-circled', template : 'demographic/labels/-info' },
            ];
        },
        recordAdded: function() {
            this.refresh();
        }
    }

});
