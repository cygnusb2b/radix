import Component from '@ember/component';
import { computed } from '@ember/object';
import ComponentQueryManager from 'ember-apollo-client/mixins/component-query-manager';
import ActionMixin from 'radix/mixins/action-mixin';
import UnapprovePost from 'radix/gql/mutations/post/unapprove';
import ApprovePost from 'radix/gql/mutations/post/approve';
import UnflagPost from 'radix/gql/mutations/post/unflag';
import FlagPost from 'radix/gql/mutations/post/flag';
import BanIdentity from 'radix/gql/mutations/identity/ban';
import UnbanIdentity from 'radix/gql/mutations/identity/unban';
import DeletePost from 'radix/gql/mutations/post/delete';
import UndeletePost from 'radix/gql/mutations/post/undelete';

export default Component.extend(ComponentQueryManager, ActionMixin, {
  item: null,

  tagName: 'li',
  classNames: ['list-group-item'],
  classNameBindings: [ 'visibilityClass' ],

  isEditPostOpen: false,
  isModerateAccountOpen: false,

  title: computed('item.{_type,stream.title}', function() {
    const prefix = this.get('item._type') === 'post-comment' ? 'Comment on ' : 'Review of ';
    const title = this.get('item.stream.title');
    return `${prefix} "${title}"`;
  }),

  displayName: computed('item.{displayName,account.displayName}', function() {
    const actName = this.get('item.account.displayName');
    const itmName = this.get('item.displayName');
    if (actName && itmName && actName !== itmName) return `${actName}<span class="d-block text-muted ml-4">as ${itmName}</span>`;
    if (itmName) return itmName;
    if (actName) return actName;
    return `an anonymous user`;
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
      if (!this.isDestroyed) this.set('isModerateAccountOpen', true);
    },
    edit() {
      if (!this.isDestroyed) this.set('isEditPostOpen', true);
    },

    async approve() {
      this.startAction();
      const mutation = ApprovePost;
      const id = this.get('item.id');
      const variables = { input: { id } };
      try {
        await this.get('apollo').mutate({ mutation, variables }, 'approvePost');
        if (!this.isDestroyed) this.set('isOpen', false);
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
        if (!this.isDestroyed) this.set('isOpen', false);
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

    async ban() {
      this.startAction();
      const mutation = BanIdentity;
      const id = this.get('item.account.id');
      const variables = { input: { id } };
      try {
        await this.get('apollo').mutate({ mutation, variables }, 'banIdentity');
        if (!this.isDestroyed) this.set('isOpen', false);
        this.get('notify').info('User banned.');
      } catch (e) {
        this.get('graphErrors').show(e)
      } finally {
        this.endAction();
      }
    },

    async unban() {
      this.startAction();
      const mutation = UnbanIdentity;
      const id = this.get('item.account.id');
      const variables = { input: { id } };
      try {
        await this.get('apollo').mutate({ mutation, variables }, 'unbanIdentity');
        if (!this.isDestroyed) this.set('isOpen', false);
        this.get('notify').info('User unbanned.');
      } catch (e) {
        this.get('graphErrors').show(e)
      } finally {
        this.endAction();
      }
    },

    async delete() {
      this.startAction();
      const mutation = DeletePost;
      const id = this.get('item.id');
      const variables = { input: { id } };
      try {
        await this.get('apollo').mutate({ mutation, variables }, 'deletePost');
        if (!this.isDestroyed) this.set('isOpen', false);
        this.get('notify').info('Post deleted.');
      } catch (e) {
        this.get('graphErrors').show(e)
      } finally {
        this.endAction();
      }
    },

    async undelete() {
      this.startAction();
      const mutation = UndeletePost;
      const id = this.get('item.id');
      const variables = { input: { id } };
      try {
        await this.get('apollo').mutate({ mutation, variables }, 'undeletePost');
        if (!this.isDestroyed) this.set('isOpen', false);
        this.get('notify').info('Post undeleted.');
      } catch (e) {
        this.get('graphErrors').show(e)
      } finally {
        this.endAction();
      }
    },
  },
});
