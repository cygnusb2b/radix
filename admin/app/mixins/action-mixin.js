import Mixin from '@ember/object/mixin';
import LoadingMixin from 'radix/mixins/loading-mixin';

export default Mixin.create(LoadingMixin, {
  isActionRunning: false,

  startAction() {
    this.showLoading();
    if (!this.isDestroyed) this.set('isActionRunning', true);
  },

  endAction() {
    if (!this.isDestroyed) this.set('isActionRunning', false);
    this.hideLoading();
  },

  startRouteAction() {
    this.showLoading();
    const controller = this.controllerFor(this.get('routeName'));
    if (!controller.isDestroyed) controller.set('isActionRunning', true);
  },

  endRouteAction() {
    const controller = this.controllerFor(this.get('routeName'));
    if (!controller.isDestroyed) controller.set('isActionRunning', false);
    this.hideLoading();
  },
});
