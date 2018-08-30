import Ember from 'ember';
const { Component } = Ember;

export default Component.extend({

  to: 'bootstrap-modals',
  backdrop: true,
  fade: true,
  show: false,
  keyboard: true,
  focus: true,
  size: null,
  contentClass: null,
  dislogClass: null,

});
