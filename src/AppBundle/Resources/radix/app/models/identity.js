import DS from 'ember-data';
import SoftDeleteable from 'radix/models/mixins/soft-deleteable';
import Timestampable from 'radix/models/mixins/timestampable';

const { attr } = DS;

export default DS.Model.extend(SoftDeleteable, Timestampable, {
    givenName:      attr('string'),
    familyName:     attr('string'),
    middleName:     attr('string'),
    salutation:     attr('string'),
    suffix:         attr('string'),
    gender:         attr('string', { defaultValue: 'Unknown' }),
    title:          attr('string'),
    companyName:    attr('string'),
    primaryEmail:   attr('string'), // @todo This should be calculated
    // addresses:      hasMany('customer-address', { inverse: 'customer' }),
    fullName:       attr('string'), // @todo This should be calculated.
    // answers:        DS.hasMany('customer-answer', { inverse: 'customer' }),
    // submissions:    DS.hasMany('customer-submission', { inverse: 'customer' }),
});
