import DS from 'ember-data';
import SoftDeleteable from 'radix/models/mixins/soft-deleteable';
import Timestampable from 'radix/models/mixins/timestampable';

const { Model, attr } = DS;

export default Model.extend(SoftDeleteable, Timestampable, {
    givenName    : attr('string'),
    familyName   : attr('string'),
    middleName   : attr('string'),
    salutation   : attr('string'),
    suffix       : attr('string'),
    gender       : attr('string', { defaultValue: 'Unknown' }),
    title        : attr('string'),
    companyName  : attr('string'),
    primaryEmail : attr('string'), // @todo This should be calculated
    fullName     : attr('string'), // @todo This should be calculated.
});
