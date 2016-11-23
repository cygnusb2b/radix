import Fragment     from 'model-fragments/fragment';
import { fragment } from 'model-fragments/attributes';

export default Fragment.extend({
    pull : fragment('integration-meta-detail'),
    push : fragment('integration-meta-detail'),
});
