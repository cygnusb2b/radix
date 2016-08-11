import Ember from 'ember';

export default Ember.Route.extend({
    model: function() {
        return this.store.createRecord('model');
    },
    renderTemplate: function(controller, model) {
        this.render();
        this.render('model.create', {
            into: 'model'
        });
    },
});
