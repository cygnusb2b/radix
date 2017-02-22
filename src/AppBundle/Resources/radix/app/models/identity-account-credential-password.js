import Fragment          from 'ember-data-model-fragments/fragment';
import attr              from 'ember-data/attr';

export default Fragment.extend({
    username  : attr('string'),
    value     : attr('string'),
    salt      : attr('string'),
    resetCode : attr('string'),
});
