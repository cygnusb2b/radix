import Ember from 'ember';
import ComponentQueryManager from 'ember-apollo-client/mixins/component-query-manager';
import ActionMixin from 'radix/mixins/action-mixin';
import deletePost from 'radix/gql/mutations/post/delete';

const { computed, Component } = Ember;

export default Component.extend(ComponentQueryManager, ActionMixin, {
  model: null,

  isOpen: false,
  isSubmitDisabled: computed.not('isActionRunning'),

  actions: {
    async deletePost() {
      this.startAction();
      const mutation = deletePost;
      const id = this.get('model.id');
      const variables = { input: { id } };
      try {
        await this.get('apollo').mutate({ mutation, variables }, 'deletePost');
        this.set('isOpen', false);
        this.get('notify').info('Post deleted.');
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
