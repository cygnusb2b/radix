import Component from '@ember/component';
import { computed } from '@ember/object';
import ComponentQueryManager from 'ember-apollo-client/mixins/component-query-manager';
import ActionMixin from 'radix/mixins/action-mixin';
import UnapprovePost from 'radix/gql/mutations/post/unapprove';
import ApprovePost from 'radix/gql/mutations/post/approve';
import UnflagPost from 'radix/gql/mutations/post/unflag';
import FlagPost from 'radix/gql/mutations/post/flag';

export default Component.extend(ComponentQueryManager, ActionMixin, {
  item: null,

  tagName: 'li',
  classNames: ['list-group-item'],
  classNameBindings: [ 'visibilityClass' ],

  isEditPostOpen: false,
  isDeletePostOpen: false,
  isModerateAccountOpen: false,

  displayName: computed('item.{displayName,account.displayName}', function() {
    const actName = this.get('item.account.displayName');
    const itmName = this.get('item.displayName');
    if (actName && itmName && actName !== itmName) return `${actName} (as ${itmName})`;
    if (itmName) return itmName;
    if (actName) return actName;
    return `an anonymous user from ${this.get('item.ipAddress')}`;
  }),

  avatar: computed('item.{picture,account.picture}', function() {
    if (this.get('item.picture')) return this.get('item.picture');
    if (this.get('item.account.picture')) return this.get('item.account.picture');
    return 'https://s3.amazonaws.com/cygnusimages/base/anonymous.jpg';
  }),

  postState: computed('item.{approved,deleted,flagged}', function() {
    if (this.get('item.deleted')) return 'deleted';
    if (this.get('item.flagged')) return 'flagged';
    return this.get('item.approved') ? 'approved' : 'unapproved';
  }),

  postVisibility: computed('postInactive', function() {
    return this.get('postInactive') ? 'hidden' : 'shown';
  }),

  postInactive: computed('item.{deleted,approved}', function() {
    return this.get('item.deleted') || this.get('item.approved') == false;
  }),

  visibilityClass: computed('postState,postInactive', function() {
    if (this.get('postInactive')) return 'list-group-item-light text-muted';

    let contextClass = 'default';
    if (this.get('postState') == 'flagged') contextClass = 'warning';

    return `list-group-item-${contextClass}`;
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
    async flag() {
      this.startAction();
      const mutation = FlagPost;
      const id = this.get('item.id');
      const variables = { input: { id } };
      try {
        await this.get('apollo').mutate({ mutation, variables }, 'flagPost');
        if (!this.isDestroyed) this.set('isOpen', false);
        this.get('notify').info('Post flagged.');
      } catch (e) {
        this.get('graphErrors').show(e)
      } finally {
        this.endAction();
      }
    },
    async unflag() {
      this.startAction();
      const mutation = UnflagPost;
      const id = this.get('item.id');
      const variables = { input: { id } };
      try {
        await this.get('apollo').mutate({ mutation, variables }, 'unflagPost');
        if (!this.isDestroyed) this.set('isOpen', false);
        this.get('notify').info('Post unflagged.');
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
