import Component from '@ember/component';
import { computed } from '@ember/object';
import ComponentQueryManager from 'ember-apollo-client/mixins/component-query-manager';
import ActionMixin from 'radix/mixins/action-mixin';

import mutation from 'radix/gql/mutations/core-application-user/update';

export default Component.extend(ComponentQueryManager, ActionMixin, {
  item: null,

  tagName: 'li',
  classNames: ['list-group-item'],

  isDeleteModalOpen: false,

  avatar: computed('item.user.picture', function() {
    if (this.get('item.user.picture')) return this.get('item.user.picture');
    return 'https://s3.amazonaws.com/cygnusimages/base/anonymous.jpg';
  }),

  roles: [ 'ROLE_USER', 'ROLE_ADMIN' ],

  primaryRole: computed.reads('item.roles.firstObject'),

  role: computed('item.roles.[]', function() {
    if (this.get('item.roles').includes('ROLE_ADMIN')) return 'Administrator';
    return 'User';
  }),

  actions: {
    async setRole(role) {
      this.startAction();
      const id = this.get('item.id');
      const payload = { roles: [role] };
      const variables = { input: { id, payload } };
      try {
        await this.get('apollo').mutate({ mutation, variables }, 'approvePost');
        if (!this.isDestroyed) this.set('isOpen', false);
        this.get('notify').info('User role updated.');
      } catch (e) {
        this.get('graphErrors').show(e)
      } finally {
        this.endAction();
      }
    },
    delete() {
      if (!this.isDestroyed) this.set('isDeleteModalOpen', true);
    },
    refresh() {
      this.sendAction('refresh');
    }
  },
});
