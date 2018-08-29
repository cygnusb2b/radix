import ListController from '../abstract-list';

export default ListController.extend({
  init() {
    this._super(...arguments);
    this.set('sortOptions', [
      { key: 'createdDate', label: 'Created' },
    ]);
    this.set('sortBy', 'createdDate');
  },
});
