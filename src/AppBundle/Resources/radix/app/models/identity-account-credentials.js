import Fragment     from 'ember-data-model-fragments/fragment';
import { fragment, fragmentArray } from 'ember-data-model-fragments/attributes';

export default Fragment.extend({
    password : fragment('identity-account-credential-password'),
    social   : fragmentArray('identity-account-credential-social'),
});
