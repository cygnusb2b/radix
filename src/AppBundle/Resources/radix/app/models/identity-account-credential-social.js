import Fragment          from 'model-fragments/fragment';
import attr              from 'ember-data/attr';

export default Fragment.extend({
    name       : attr('string'),
    identifier : attr('string'),
});
