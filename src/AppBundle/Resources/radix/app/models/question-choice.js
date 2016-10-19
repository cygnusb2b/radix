import DS             from 'ember-data';
import Sequenceable   from 'radix/models/mixins/sequenceable';
import SoftDeleteable from 'radix/models/mixins/soft-deleteable';
import Timestampable  from 'radix/models/mixins/timestampable';

const { Model, attr, belongsTo } = DS;

export default Model.extend(Sequenceable, SoftDeleteable, Timestampable, {
    alternateId : attr('string'),
    choiceType  : attr('string', { defaultValue: 'standard' }),
    description : attr('string'),
    name        : attr('string'),
    question    : belongsTo('question'),
});
