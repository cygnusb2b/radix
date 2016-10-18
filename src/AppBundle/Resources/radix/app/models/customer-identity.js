import DS from 'ember-data';
import Customer from 'radix/models/customer';

export default Customer.extend({
    primaryEmail:   DS.attr('string'),
    lastSeen:       DS.attr('date'),
    account:        DS.belongsTo('customer-account'),
});
