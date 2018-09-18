import Service from '@ember/service';
import $ from 'jquery';

export default Service.extend({
  selector: '.loading-overlay',

  show: function () {
    this._getElement().show();
  },

  hide: function () {
    this._getElement().hide();
  },

  toggle: function () {
    this._getElement().toggle();
  },

  _getElement: function () {
    return $(this.get('selector'));
  }

});
