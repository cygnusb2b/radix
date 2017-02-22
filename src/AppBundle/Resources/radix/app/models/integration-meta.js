import Fragment     from 'ember-data-model-fragments/fragment';
import { fragment, fragmentArray } from 'ember-data-model-fragments/attributes';

export default Fragment.extend({
    pull : fragment('integration-meta-detail'),
    push : fragmentArray('integration-meta-detail'),
});
