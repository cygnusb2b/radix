import Ember from 'ember';

const { inject: { service }, Route } = Ember;

export default Route.extend({

    query: service('model-query'),

    model: function() {
        return this.get('query').execute('identity-internal');
    },

    actions: {
        recordAdded: function() {
            this.refresh();
        }
    }

});
