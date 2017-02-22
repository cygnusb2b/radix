import Model             from 'ember-data/model';
import { belongsTo }     from 'ember-data/relationships';
import { fragment }      from 'ember-data-model-fragments/attributes';
import IdentityEmailable from 'radix/models/mixins/identity-emailable';
import Timestampable     from 'radix/models/mixins/timestampable';

export default Model.extend(IdentityEmailable, Timestampable, {
    verification : fragment('identity-account-email-verification'),
    account      : belongsTo('identity-account'),
});
