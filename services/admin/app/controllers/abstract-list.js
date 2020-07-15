import Ember from 'ember';

const { Controller, computed } = Ember;

export default Controller.extend({
  /**
   * Query params
   */
  queryParams: null,
  phrase: '',
  first: 20,
  after: null,
  ascending: false,
  sortBy: null,

  /**
   * Pagination
   */
  totalCount: 0,
  hasNextPage: false,
  endCursor: null,

  isSortDisabled: computed('phrase.length', function() {
    return this.get('phrase.length') > 0;
  }),

  init() {
    this._super(...arguments);
    this.set('queryParams', ['first', 'after', 'sortBy', 'ascending', 'phrase']);

    // Should be overriden by the specific controller for different options.
    this.set('sortOptions', [
      { key: 'createdAt', label: 'Created' },
    ]);
    this.set('limitOptions', [10, 20, 50, 100, 200]);
    this.set('sortBy', 'createdAt');
  },

  actions: {
    search(phrase) {
      this.set('phrase', phrase);
    },
  },
});
