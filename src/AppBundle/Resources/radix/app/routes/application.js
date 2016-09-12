import Ember from 'ember';

export default Ember.Route.extend({
    actions: {
        error: function(error) {
            console.warn('route error handling needed', error);
        }
    }
});
