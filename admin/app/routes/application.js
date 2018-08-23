import Ember from 'ember';
import ApplicationRouteMixin from 'ember-simple-auth/mixins/application-route-mixin';

const { inject: { service }, Route } = Ember;

export default Route.extend(ApplicationRouteMixin, {

    userManager: service(),

    loading: service(),

    session: service('session'),

    beforeModel: function() {
        return this._loadCurrentUser();
    },

    sessionAuthenticated: function() {
        this._super(...arguments);
        this._loadCurrentUser().catch(() => this.get('session').invalidate());
    },

    setupController: function(controller, model) {
        controller.set('session', this.get('session'));
        controller.set('userManager', this.get('userManager'));
        this._super(controller, model);
    },

    actions: {
        refreshApp: function() {
            let loading = this.get('loading');
            loading.show();
            this.refresh().finally(() => loading.hide());
        }
    },

    _loadCurrentUser: function() {
        return this.get('userManager').load().then(() => this._selectApplication());
    },

    _selectApplication: function() {
        if (!this.get('session.isAuthenticated')) {
            return;
        }

        let currentId  = this.get('session.data.application.id');
        let available  = this.get('session.data.authenticated.applications') || [];
        let defaultApp = available[0];
        let canAccess  = -1 !== available.mapBy('id').indexOf(currentId);

        if ((currentId && !canAccess) || !currentId) {
            // If no app selected, or a selected app and the user can no longer access, reset selection to default.
            this.get('session').set('data.application', defaultApp);
        }
    }
});
