import { inject } from '@ember/service';
import { typeOf } from '@ember/utils';
import Service from '@ember/service';
import $ from 'jquery';

export default Service.extend({

  utility: inject('model-utility'),
  selector: '#confirm-modal',
  transition: null,
  onUnsavedConfirm: null,
  onDeleteConfirm: null,
  confirmValue: null,

  unsaved: function (model, confirmValue, transition, checkRels, onConfirm, onClean) {
    model = model || {};
    onClean = this._fillCallback(onClean);
    onConfirm = this._fillCallback(onConfirm);
    let element = this.getElementFor('unsaved');

    // Clear any previously set transition.
    this.set('transition', null);

    if (this.get('utility').isDirty(model, checkRels)) {
      this.set('confirmValue', confirmValue);
      this.set('onUnsavedConfirm', onConfirm);

      if (transition) {
        transition.abort();
        this.set('transition', transition);
      }

      element.modal();
    } else {
      onClean();
    }
  },

  delete: function (model, confirmValue, onConfirm) {
    model = model || {};
    onConfirm = this._fillCallback(onConfirm);
    let element = this.getElementFor('delete');

    this.set('confirmValue', confirmValue);
    this.set('onDeleteConfirm', onConfirm);

    element.modal();
  },

  getElementFor: function (type) {
    return $(this.get('selector') + '-' + type);
  },

  _fillCallback: function (callback) {
    return 'function' === typeOf(callback) ? callback : function () { };
  }

});
