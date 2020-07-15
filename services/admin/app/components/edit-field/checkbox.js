import Ember from 'ember';

export default Ember.Component.extend({
    checked: false,
    disabled: false,
    tagName: 'label',
    label: null,
    classNames: ['custom-control', 'custom-checkbox'],
});
