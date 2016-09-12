import Ember from 'ember';

export default Ember.Component.extend({

    tagName: 'select',

    disabled: false,

    attributeBindings: ['disabled'],

    classNames: ['form-control', 'custom-select'],

    defaultLabel: null,
    value: null,
    options: [],

    change: function(event) {
        let value = event.target.value;
        this.set('value', value);
    }

});
