import ListRoute from 'radix/routes/-list-route';

export default ListRoute.extend({

  model(params) {
    return this.retrieveModel('post', params, { deleted: false });
  }
});
