import Ember from 'ember';

const { computed } = Ember;

export default Ember.Component.extend({
  item: null,

  displayName: computed('item.displayName', 'item.account.displayName', function() {
    if (this.get('item.displayName')) return this.get('item.displayName');
    if (this.get('item.account.displayName')) return this.get('item.account.displayName');
    return `an anonymous user from ${this.get('item.ipAddress')}`;
  }),

  avatar: computed('item.picture', 'item.account.picture', function() {
    if (this.get('item.picture')) return this.get('item.picture');
    if (this.get('item.account.picture')) return this.get('item.account.picture');
    return 'https://s3.amazonaws.com/cygnusimages/base/anonymous.jpg';
  })
});
