import Model             from 'ember-data/model';
import attr              from 'ember-data/attr';
import { fragmentArray } from 'model-fragments/attributes';
import SoftDeleteable    from 'radix/models/mixins/soft-deleteable';
import Timestampable     from 'radix/models/mixins/timestampable';

export default Model.extend(SoftDeleteable, Timestampable, {
    givenName    : attr('string'),
    familyName   : attr('string'),
    middleName   : attr('string'),
    salutation   : attr('string'),
    suffix       : attr('string'),
    gender       : attr('string', { defaultValue: 'Unknown' }),
    title        : attr('string'),
    companyName  : attr('string'),
    addresses    : fragmentArray('identity-address'),
    phones       : fragmentArray('identity-phone'),
});
