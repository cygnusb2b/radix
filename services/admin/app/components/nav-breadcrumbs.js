import Component from '@ember/component';

export default Component.extend({
  tagName: 'nav',
  attributeBindings: ['aria-label'],
  wrapperClass: 'breadcrumb border-0 h4 bg-transparent my-1',

  'aria-label': 'breadcrumb',
});
