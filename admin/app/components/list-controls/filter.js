import Component from '@ember/component';
import { computed } from '@ember/object';
import { isArray } from '@ember/array';
import MenuMixin from 'radix/components/list-controls/menu-mixin';

export default Component.extend(MenuMixin, {
  classNames: ['btn-group'],
  attributeBindings: ['role', 'aria-label'],

  role: 'group',
  'aria-label': 'Sort filter',

  /**
   * The filterBy field value, e.g. `createdAt` or `name`.
   * @public
   * @type {string}
   */
  filterBy: null,

  /**
   * Whether the sort dropdown control is completely disabled.
   * @public
   * @type {boolean}
   */
  disabled: false,

  /**
   * The class to apply to buttons within this group
   * @public
   * @type {string}
   */
  buttonClass: 'btn-primary',

  /**
   * Based on the `filterBy` value, computes the selected sort object.
   * For example, if the `filterBy` value equals `createdAt`, this would return
   * something like `{ key: 'createdAt', label: 'Created' }`.
   */
  selected: computed('options.[]', 'filterBy', function() {
    return this.get('options').findBy('key', this.get('filterBy'));
  }),

  clearDisabled: computed.bool('selected'),

  /**
   * Displays filtered sort options by removing the currently selected `filterBy` value.
   * Returns an array of sort option objects.
   */
  filteredOptions: computed('options.[]', 'filterBy', function() {
    return this.get('options').rejectBy('key', this.get('filterBy'));
  }),

  /**
   * Initializes the component.
   * If the `options` property is not an array, it will set it as an empty array.
   */
  init() {
    this._super(...arguments);
    if (!isArray(this.get('options'))) {
      this.set('options', []);
    }
  },

  actions: {
    clearFilter() {
      this.set('filterBy', null);
    },
    filterBy(key) {
      this.set('filterBy', key);
    },
  },
});
