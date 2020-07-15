import Ember from 'ember';

const { computed, typeOf, Component, inject: { service } } = Ember;

export default Component.extend({
  modelQuery: service(),
  store: service(),

  application: null,
  refresh: 0,

  _itemsNew: null,
  _items: computed('application', 'refresh', function() {
    const application = this.get('application');
    return this.get('modelQuery').execute('core-application-user', { application: application.get('id') });
  }),

  items: computed('_items.[]', '_itemsNew.[]', function() {
    return this.get('_items').toArray().concat(this.get('_itemsNew'));
  }),


  init() {
    this.set('_itemsNew', []);
    this._super(...arguments);
  },

  actions: {
    refresh: function() {
      this.set('refresh', this.get('refresh') + 1);
      const itemsNew = this.get('_itemsNew');
      itemsNew.forEach(function(model) {
        if (model.get('isNew') === false) itemsNew.removeObject(model);
      });
    },
    create: function() {
      const application = this.get('application');
      const newObj = this.get('store').createRecord('core-application-user', { application });
      this.get('_itemsNew').pushObject(newObj);
    },
    cancelNew: function(model) {
      this.get('_itemsNew').removeObject(model);
    },
  },

});
