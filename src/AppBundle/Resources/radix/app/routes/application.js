import Ember from 'ember';
import ApplicationRouteMixin from 'ember-simple-auth/mixins/application-route-mixin';

const { inject: { service }, Route } = Ember;

export default Route.extend(ApplicationRouteMixin, {

    session: service('session'),

    setupController: function(controller, model) {
        controller.set('session', this.get('session'));
        this._super(controller, model);
    },
});
