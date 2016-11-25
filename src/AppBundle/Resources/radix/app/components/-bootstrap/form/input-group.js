import Ember from 'ember';
import Group from 'radix/components/-bootstrap/form/group';

const { computed } = Ember;

export default Group.extend({
    key         : null,
    value       : null,
    label       : null,
    type        : 'text',
    placeholder : null,
    helpText    : null,

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
});
