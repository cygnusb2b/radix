import Ember from 'ember';

const { Component, computed } = Ember;

const ButtonComponent = Component.extend({
  tagName: 'button',
  classNames: ['btn'],
  classNameBindings: ['_typeClass', 'role', '_sizeClass', '_blockClass', '_disabledClass'],
  attributeBindings: ['disabled', 'disabled:aria-disabled', 'type'],
  role: 'button',
  type: 'button',

  outline: false, // Whether to outline the button.
  styleType: 'primary', // Either primary, secondary, success, info, warning, danger, or link.
  size: null, // If null, will be regular size, otherwise can pass large or small.
  isBlock: false, // Whether the button should be displayed as a block level button.
  disabled: false, // Whether the button us disabled.

  _typeClass: computed('styleType', 'outline', function() {
    const outlineClass = this.get('outline') ? 'outline-' : '';
    return `btn-${outlineClass}${this.get('styleType')}`;
  }),

  _sizeClass: computed('size', function() {
    switch (this.get('size')) {
      case 'large':
        return 'btn-lg';
      case 'small':
        return 'btn-sm';
      default:
        return;
    }
  }),

  _blockClass: computed('isBlock', function() {
    return this.get('isBlock') ? 'btn-block' : false;
  }),

  _disabledClass: computed('disabled', 'tagName', function() {
    const tagName = this.get('tagName');
    if ('button' === tagName) {
      return false;
    }
    return this.get('disabled') ? 'disabled' : false;
  }),
});

ButtonComponent.reopenClass({
  positionalParams: ['styleType'],
});

export default ButtonComponent;
