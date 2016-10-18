import DS from 'ember-data';
import Customer from 'radix/models/customer';

export default Customer.extend({
    displayName:    DS.attr('string'),
    picture:        DS.attr('string'),
    roles:          DS.attr(),

    emails:         DS.hasMany('customer-email', { inverse: 'account' }),
    identities:     DS.hasMany('customer-identity', { inverse: 'account' }),
});
