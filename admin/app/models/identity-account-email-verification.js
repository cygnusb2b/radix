import Fragment from 'ember-data-model-fragments/fragment';
import attr     from 'ember-data/attr';

export default Fragment.extend({
    verified      : attr('boolean', { defaultValue: false }),
    token         : attr('string'),
    completedDate : attr('date'),
    generatedDate : attr('date'),
});
