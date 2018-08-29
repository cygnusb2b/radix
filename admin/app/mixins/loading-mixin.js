import Ember from 'ember';

const { Mixin, inject: { service } } = Ember;

export default Mixin.create({
  loadingDisplay: service(),

  showLoading() {
    this.get('loadingDisplay').show();
  },
  hideLoading() {
    this.get('loadingDisplay').hide();
  },
});
