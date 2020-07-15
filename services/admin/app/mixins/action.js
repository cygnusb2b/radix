import Mixin from '@ember/object/mixin';

export default Mixin.create({
  isActionRunning: false,

  sendEventAction(name, ...args) {
    const fn = this.get(name);
    if (typeof fn === 'function') return fn(...args);
  },

  startAction() {
    this.set('isActionRunning', true);
  },

  endAction() {
    if (!this.get('isDestroyed')) {
      this.set('isActionRunning', false);
    }
  },
});
