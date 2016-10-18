import DS from 'ember-data';
import Timestampable from 'radix/models/mixins/timestampable';

export default DS.Model.extend(Timestampable, {
    name:       DS.attr('string'),
    isPrimary:  DS.attr('boolean', { defaultValue: false }),
    value:      DS.attr('string'),
    account:    DS.belongsTo('customer-account')
});
