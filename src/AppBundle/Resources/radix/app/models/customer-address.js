import DS from 'ember-data';
import Timestampable from 'radix/models/mixins/timestampable';

export default DS.Model.extend(Timestampable, {
    name:               DS.attr('string'),
    isPrimaryBilling:   DS.attr('boolean', { defaultValue: false }),
    isPrimaryMailing:   DS.attr('boolean', { defaultValue: false }),
    companyName:        DS.attr('string'),
    street:             DS.attr('string'),
    extra:              DS.attr('string', { defaultValue: 'Unknown' }),
    city:               DS.attr('string'),
    region:             DS.attr('string'),
    regionCode:         DS.attr('string'),
    country:            DS.attr('string'),
    countryCode:        DS.attr('string'),
    postalCode:         DS.attr('string'),
    customer:           DS.belongsTo('customer', { polymorphic: true })
});
