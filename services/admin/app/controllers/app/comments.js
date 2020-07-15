import ListController from '../abstract-list';

export default ListController.extend({
  init() {
    this._super(...arguments);
    this.set('sortOptions', [
      { key: 'createdDate', label: 'Posted' },
    ]);
    this.set('sortBy', 'createdDate');
    this.set('filterOptions', [
      { key: 'active',  label: 'Show active posts' },
      { key: 'all',     label: 'Show all posts' },
      { key: 'flagged', label: 'Only flagged posts' },
      { key: 'hidden',  label: 'Only hidden posts' },
      { key: 'deleted', label: 'Only deleted posts' },
    ])
    this.set('filterBy', 'active');
  },
});
