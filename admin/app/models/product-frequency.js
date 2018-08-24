import Fragment from 'ember-data-model-fragments/fragment';
import attr     from 'ember-data/attr';

export default Fragment.extend({
    freq     : attr('string', { defaultValue: 'variable' }),
    interval : attr('string', { defaultValue: 1 }),
});
