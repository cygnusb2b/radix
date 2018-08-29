import Ember from 'ember';
import ApplicationRouteMixin from 'ember-simple-auth/mixins/application-route-mixin';
import ActionMixin from 'radix/mixins/action-mixin';

const { inject: { service }, Route, get } = Ember;

export default Route.extend(ApplicationRouteMixin, ActionMixin, {

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
        },

        showLoading() {
            this.showLoading();
          },

          hideLoading() {
            this.hideLoading();
          },

          transitionTo(name) {
            return this.transitionTo(name);
          },

          transitionWithModel(routeName, model) {
            return this.transitionTo(routeName, get(model, 'id'));
          },

          scrollToTop() {
            window.scrollTo(0, 0);
          },

          /**
           *
           * @param {*} transition
           */
          loading(transition) {
            this.showLoading();
            transition.finally(() => this.hideLoading());
          },

          /**
           *
           * @param {Error} e
           */
          error(e) {
            if (this.get('graphErrors').isReady()) {
              this.get('graphErrors').show(e);
            } else {
              this.intermediateTransitionTo('application_error', e);
            }
          },
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
