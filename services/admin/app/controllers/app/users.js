import ListController from '../abstract-list';

export default ListController.extend({
  isSearchDisabled: true,
  isCreateModalOpen: false,

  actions: {
    search() {
    },
    addUser() {
      this.set('isCreateModalOpen', true);
    }
  },

  init() {
    this._super(...arguments);
    this.set('sortOptions', [
      { key: 'updatedDate', label: 'Updated' },
      { key: 'createdDate', label: 'Created' },
    ]);
    this.set('sortBy', 'createdDate');
    this.set('filterOptions', [
      { key: 'all',     label: 'Show all users' },
      { key: 'role_user', label: 'Only users' },
      { key: 'role_admin', label: 'Only admins' },
    ])
    this.set('filterBy', 'all');
  },
});
