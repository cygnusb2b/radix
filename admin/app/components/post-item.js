import Component from '@ember/component';
import { computed } from '@ember/object';
import ComponentQueryManager from 'ember-apollo-client/mixins/component-query-manager';
import ActionMixin from 'radix/mixins/action-mixin';
import UnapprovePost from 'radix/gql/mutations/post/unapprove';
import ApprovePost from 'radix/gql/mutations/post/approve';

export default Component.extend(ComponentQueryManager, ActionMixin, {
  item: null,

  isEditPostOpen: false,
  isDeletePostOpen: false,
  isModerateAccountOpen: false,

  displayName: computed('item.{displayName,account.displayName}', function() {
    if (this.get('item.displayName')) return this.get('item.displayName');
    if (this.get('item.account.displayName')) return this.get('item.account.displayName');
    return `an anonymous user from ${this.get('item.ipAddress')}`;
  }),

  avatar: computed('item.{picture,account.picture}', function() {
    if (this.get('item.picture')) return this.get('item.picture');
    if (this.get('item.account.picture')) return this.get('item.account.picture');
    return 'https://s3.amazonaws.com/cygnusimages/base/anonymous.jpg';
  }),

  actions: {
    moderate() {
      this.set('isModerateAccountOpen', true);
    },
    edit() {
      this.set('isEditPostOpen', true);
    },
    async approve() {
      this.startAction();
      const mutation = ApprovePost;
      const id = this.get('item.id');
      const variables = { input: { id } };
      try {
        await this.get('apollo').mutate({ mutation, variables }, 'approvePost');
        this.set('isOpen', false);
        this.get('notify').info('Post approved.');
      } catch (e) {
        this.get('graphErrors').show(e)
      } finally {
        this.endAction();
      }
    },
    async unapprove() {
      this.startAction();
      const mutation = UnapprovePost;
      const id = this.get('item.id');
      const variables = { input: { id } };
      try {
        await this.get('apollo').mutate({ mutation, variables }, 'unapprovePost');
        this.set('isOpen', false);
        this.get('notify').info('Post unapproved.');
      } catch (e) {
        this.get('graphErrors').show(e)
      } finally {
        this.endAction();
      }
    },
    delete() {
      this.set('isDeletePostOpen', true);
    },
  },
});
