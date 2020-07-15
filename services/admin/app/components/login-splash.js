import { inject } from '@ember/service';
import { computed } from '@ember/object';
import Component from '@ember/component';
import ComponentQueryManager from 'ember-apollo-client/mixins/component-query-manager';
import ActionMixin from 'radix/mixins/action-mixin';

import mutation from 'radix/gql/mutations/core-application-user/reset';

export default Component.extend(ComponentQueryManager, ActionMixin, {
  isOpen: false,
  isSubmitDisabled: computed.bool('isActionRunning'),

  username: null,
  password: null,
  errorMessage: null,
  isResetting: false,

  session: inject('session'),
  loading: inject('loading'),

  actions: {
    toggleReset() {
      this.set('isResetting', !this.get('isResetting'));
    },

    async reset() {
      if (!this.get('username')) {
        this.get('graphErrors').show(new Error('You must provide an email address!'));
        return;
      }
      this.startAction();
      const email = this.get('username');
      const variables = { email };
      try {
        await this.get('apollo').mutate({ mutation, variables });
        if (!this.isDestroyed) this.set('isOpen', false);
        this.get('notify').info('Reset request processed.');
        this.sendAction('onComplete');
      } catch (e) {
        this.get('graphErrors').show(e)
      } finally {
        this.endAction();
      }
    },
    authenticate: function () {
      let loading = this.get('loading');

      loading.show();
      this.set('errorMessage', null);
      let { username, password } = this.getProperties('username', 'password');
      this.get('session')
        .authenticate('authenticator:core', username, password)
        .catch((error) => this.set('errorMessage', error.detail || null))
        .finally(() => loading.hide())
        ;
    },
    clear() {
      this.setProperties({
        username: '',
        password: '',
      });
    },
  }
});
