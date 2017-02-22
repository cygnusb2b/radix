import Fragment          from 'ember-data-model-fragments/fragment';
import attr              from 'ember-data/attr';

export default Fragment.extend({
    identifier  : attr('string'),
    description : attr('string'),
    phoneType   : attr('string', { defaultValue: 'Phone' }),
    number      : attr('string'),
    isPrimary   : attr('boolean', { defaultValue: false }),
});
