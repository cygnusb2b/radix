import Ember from 'ember';

const { Controller, computed } = Ember;

export default Controller.extend({
  queryParams: ['limit', 'sort', 'ascending', 'phrase', 'page'],
  limit: 25,
  ascending: false,
  sort: 'name',
  phrase: '',
  page: 1,
  reload: false,

  phraseInput: '',
  limitOptions: [25, 50, 100, 200],
  sortOptions: [
    { key: 'name', label: 'Name' },
    { key: 'relevance', label: 'Relevance' },
  ],

  filteredSortOptions: computed('sortOptions', 'sort', 'isSortDisabled', function() {
    let filtered = this.get('sortOptions').rejectBy('key', this.get('sort'));
    if (!this.get('isSortDisabled')) {
      filtered = filtered.rejectBy('key', 'relevance');
    }
    return filtered;
  }),

  filteredLimitOptions: computed('limitOptions', 'limit', function() {
    return this.get('limitOptions').reject(item => {
      return item === this.get('limit');
    });
  }),

  isSortDisabled: computed('phraseInput.length', function() {
    return this.get('phraseInput.length') > 0;
  }),

  isSearchDisabled: computed('phraseInput', function() {
    return !this.get('phraseInput');
  }),

  isPageLeftDisabled: computed('page', function() {
    return 1 === this.get('page');
  }),

  isPageRightDisabled: computed('model.length', 'limit', function() {
    return this.get('model.length') < this.get('limit');
  }),

  phraseValue: computed('phrase', 'phraseInput', {
    get() {
      const input = this.get('phraseInput');
      const phrase = this.get('phrase');
      if (!input && phrase) {
        // Set the input field to the query phrase.
        // Ensures that, if the `phrase` query param is present, and the route is hard-reloaded,
        // the `phraseInput` value will contain the `phrase` value.
        this.set('phraseInput', phrase);
      }
      return this.get('phraseInput');
    },
    set(key, value) {
      this.set('phraseInput', value);
      return value;
    }
  }),

  selectedAscending: computed('ascending', 'isSortDisabled', function() {
    if (this.get('isSortDisabled')) {
      return true;
    }
    return this.get('ascending');
  }),

  selectedSort: computed('sortOptions', 'sort', 'isSortDisabled', function() {
    let key = this.get('isSortDisabled') ? 'relevance' : this.get('sort');
    return this.get('sortOptions').findBy('key', key);
  }),

  actions: {
    clearSearch() {
      this.set('phrase', '');
      this.set('phraseInput', '');
    },
    incrementPage(value) {
      this.set('page', this.get('page') + value);
    },
    resetPagination() {
      this.set('page', 1);
    },
    search() {
      this.set('phrase', this.get('phraseInput'));
    },
    selectAll(event) {
      event.target.select();
    },
    toggleDirection() {
      this.set('ascending', !this.get('ascending'));
    },
  },
});
