import Fragment          from 'ember-data-model-fragments/fragment';
import attr              from 'ember-data/attr';

export default Fragment.extend({
    identifier  : attr('string'),
    description : attr('string'),
    isPrimary   : attr('boolean', { defaultValue: false }),
    companyName : attr('string'),
    street      : attr('string'),
    extra       : attr('string'),
    city        : attr('string'),
    region      : attr('string'),
    regionCode  : attr('string'),
    country     : attr('string'),
    countryCode : attr('string'),
    postalCode  : attr('string'),
});
