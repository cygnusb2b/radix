import Ember from 'ember';
import Group from 'radix/components/-bootstrap/form/group';

const { String: { dasherize }, computed } = Ember;

export default Group.extend({
    key                 : null,
    value               : null,
    label               : null,
    type                : 'text',
    placeholder         : null,
    helpText            : null,
    hideLabel           : false,
    hidePlaceholder     : false,
    calculateLabel      : true,
    calculatePlaceholder: true,

    fieldId     : computed('key', function() {
        let key = this.get('key');
        if (!key) {
            return;
        }
        return 'field-' + key;
    }),

    helpId      : computed('fieldId', 'helpText', function() {
        let text = this.get('helpText');
        let id   = this.get('fieldId');

        if (!text || !id) {
            return;
        }
        return id + '-help';
    }),

    calcLabel : computed('key', 'label', function() {
        let label = this.get('label');
        if (!this.get('calculateLabel') || label) {
            return label;
        }
        let parts = dasherize(this.get('key')).split('-');
        for (var i = 0; i < parts.length; i++) {
            parts[i] = parts[i].charAt(0).toUpperCase() + parts[i].slice(1);
        }
        return parts.join(' ');
    }),

    calcPlaceholder : computed('key', 'placeholder', function() {
        let placeholder = this.get('placeholder');
        if (!this.get('calculatePlaceholder') || placeholder) {
            return placeholder;
        }
        return this.get('calcLabel');
    }),

});
