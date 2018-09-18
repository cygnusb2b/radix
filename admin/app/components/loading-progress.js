import Component from '@ember/component';
import { computed } from '@ember/object';
import LoadingMixin from 'radix/mixins/loading-mixin';

export default Component.extend(LoadingMixin, {
  classNames: ['loading', 'progress'],
  progressBackground: 'bg-dark',
  show: computed.readOnly('loadingDisplay.isShowing'),
});
