import Ember from 'ember';

export default Ember.Component.extend({
    value: null,
    label: null,
    selectValue: null,
    tagName: 'option',
    attributeBindings: ['selected', 'value'],

    selected: Ember.computed('selectValue', function() {
        return this.get('value') === this.get('selectValue');
    }),

    didReceiveAttrs: function() {
        if (!this.get('value')) {
            this.set('value', this.get('label'));
        }
        if (!this.get('label')) {
            this.set('label', this.get('value'));
        }
    }
});
