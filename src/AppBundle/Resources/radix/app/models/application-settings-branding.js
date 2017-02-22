import Fragment          from 'ember-data-model-fragments/fragment';
import attr              from 'ember-data/attr';

export default Fragment.extend({
    logo : attr('string'),
    name : attr('string'),
});
