import ListController from 'radix/controllers/-list-controller';

export default ListController.extend({
  sort: 'createdDate',

  init : function() {
    this._super(...arguments);
  },
});