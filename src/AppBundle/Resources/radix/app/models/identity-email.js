import Fragment          from 'model-fragments/fragment';
import attr              from 'ember-data/attr';
import IdentityEmailable from 'radix/models/mixins/identity-emailable';

export default Fragment.extend(IdentityEmailable, {
    identifier : attr('string'),
});
