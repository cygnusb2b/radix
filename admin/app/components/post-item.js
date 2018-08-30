import Ember from 'ember';

const { computed } = Ember;

export default Ember.Component.extend({
  item: null,

  isEditPostOpen: false,
  isDeletePostOpen: false,
  isModerateAccountOpen: false,

  displayName: computed('item.displayName', 'item.account.displayName', function() {
    if (this.get('item.displayName')) return this.get('item.displayName');
    if (this.get('item.account.displayName')) return this.get('item.account.displayName');
    return `an anonymous user from ${this.get('item.ipAddress')}`;
  }),

  avatar: computed('item.picture', 'item.account.picture', function() {
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
    approve() {
      // ...
      console.warn('approve nyi');
    },
    unapprove() {
      // ...
      console.warn('unapprove nyi');
    },
    delete() {
      this.set('isDeletePostOpen', true);
    },

  },
});
