import Component from '@ember/component';
import { computed } from '@ember/object';
import ComponentQueryManager from 'ember-apollo-client/mixins/component-query-manager';
import ActionMixin from 'radix/mixins/action-mixin';
import mutation from 'radix/gql/mutations/core-application-user/remove';

export default Component.extend(ComponentQueryManager, ActionMixin, {
  model: null,

  isOpen: false,
  isSubmitDisabled: computed.bool('isActionRunning'),

  actions: {
    async delete() {
      this.startAction();
      const id = this.get('model.id');
      const variables = { input: { id } };
      try {
        await this.get('apollo').mutate({ mutation, variables });
        if (!this.isDestroyed) this.set('isOpen', false);
        this.get('notify').info('User removed.');
        this.sendAction('onComplete');
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
