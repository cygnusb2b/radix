import Ember from 'ember';
import LoadingMixin from 'radix/mixins/loading-mixin';

const { Component, computed } = Ember;

export default Component.extend(LoadingMixin, {
  classNames: ['loading', 'progress'],
  progressBackground: 'bg-dark',
  show: computed.readOnly('loadingDisplay.isShowing'),
});
