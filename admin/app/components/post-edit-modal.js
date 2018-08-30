import Ember from 'ember';
import ComponentQueryManager from 'ember-apollo-client/mixins/component-query-manager';
import ActionMixin from 'radix/mixins/action-mixin';
import updatePost from 'radix/gql/mutations/post/update';

const { computed, Component } = Ember;

export default Component.extend(ComponentQueryManager, ActionMixin, {
  model: null,

  title: computed.oneWay('model.title'),
  body: computed.oneWay('model.body'),
  picture: computed.oneWay('model.picture'),
  displayName: computed.oneWay('model.displayName'),

  isOpen: false,
  isSubmitDisabled: computed.not('isActionRunning'),

  actions: {
    async updatePost() {
      this.startAction();
      const mutation = updatePost;
      const id = this.get('model.id');
      const { title, body, picture, displayName } = this.getProperties('title,body,picture,displayName');
      const input = { id, title, body, picture, displayName };
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
