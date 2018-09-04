import Ember from 'ember';
import ComponentQueryManager from 'ember-apollo-client/mixins/component-query-manager';
import ActionMixin from 'radix/mixins/action-mixin';
import updatePost from 'radix/gql/mutations/post/update';

const { computed, Component } = Ember;

export default Component.extend(ComponentQueryManager, ActionMixin, {
  model: null,

  title: computed.oneWay('model.title'),
  body: computed.oneWay('model.body'),
  displayName: computed.oneWay('model.displayName'),

  isOpen: false,
  isSubmitDisabled: computed.bool('isActionRunning'),

  actions: {
    async updatePost() {
      this.startAction();
      const mutation = updatePost;
      const id = this.get('model.id');
      const payload = this.getProperties(['title','body','displayName']);
      const input = { id, payload };
      const variables = { input };
      try {
        await this.get('apollo').mutate({ mutation, variables }, 'updatePost');
        this.set('isOpen', false);
        this.get('notify').info('Password successfully changed.');
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
