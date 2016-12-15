import Fragment          from 'model-fragments/fragment';
import attr              from 'ember-data/attr';

export default Fragment.extend({
    enabled      : attr('boolean'),
    locked       : attr('boolean'),
    shadowbanned : attr('boolean'),
});
