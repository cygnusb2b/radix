import Ember from 'ember';
import ComponentQueryManager from 'ember-apollo-client/mixins/component-query-manager';
import ActionMixin from 'radix/mixins/action-mixin';
import mutation from 'radix/gql/mutations/identity/update';

const { computed, Component } = Ember;

export default Component.extend(ComponentQueryManager, ActionMixin, {
  model: null,

  picture: computed.oneWay('model.picture'),
  displayName: computed.oneWay('model.displayName'),
  givenName: computed.oneWay('model.givenName'),
  familyName: computed.oneWay('model.familyName'),

  isOpen: false,
  isSubmitDisabled: computed.bool('isActionRunning'),

  actions: {
    async updateIdentity() {
      this.startAction();
      const id = this.get('model.id');
      const payload = this.getProperties(['picture','displayName']);
      const input = { id, payload };
      const variables = { input };
      try {
        await this.get('apollo').mutate({ mutation, variables }, 'updateIdentity');
        if (!this.isDestroyed) this.set('isOpen', false);
        this.get('notify').info('User updated.');
      } catch (e) {
        this.get('graphErrors').show(e)
      } finally {
        this.endAction();
      }
    },

    clear() {
    },
  },

});
