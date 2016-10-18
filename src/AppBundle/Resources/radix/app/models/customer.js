import DS from 'ember-data';
import SoftDeleteable from 'radix/models/mixins/soft-deleteable';
import Timestampable from 'radix/models/mixins/timestampable';

export default DS.Model.extend(SoftDeleteable, Timestampable, {
    givenName:      DS.attr('string'),
    familyName:     DS.attr('string'),
    middleName:     DS.attr('string'),
    salutation:     DS.attr('string'),
    suffix:         DS.attr('string'),
    gender:         DS.attr('string', { defaultValue: 'Unknown' }),
    title:          DS.attr('string'),
    companyName:    DS.attr('string'),
    addresses:      DS.hasMany('customer-address', { inverse: 'customer' }),
    // answers:        DS.hasMany('customer-answer', { inverse: 'customer' }),
    // submissions:    DS.hasMany('customer-submission', { inverse: 'customer' }),
});
