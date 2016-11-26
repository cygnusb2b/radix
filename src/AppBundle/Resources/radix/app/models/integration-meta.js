import Fragment     from 'model-fragments/fragment';
import { fragment, fragmentArray } from 'model-fragments/attributes';

export default Fragment.extend({
    pull : fragment('integration-meta-detail'),
    push : fragmentArray('integration-meta-detail'),
});
