import Fragment     from 'model-fragments/fragment';
import { fragment, fragmentArray } from 'model-fragments/attributes';

export default Fragment.extend({
    password : fragment('identity-account-credential-password'),
    social   : fragmentArray('identity-account-credential-social'),
});
