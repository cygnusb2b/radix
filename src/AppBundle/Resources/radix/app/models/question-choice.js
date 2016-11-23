import Model          from 'ember-data/model';
import attr           from 'ember-data/attr';
import { belongsTo }  from 'ember-data/relationships';
import Sequenceable   from 'radix/models/mixins/sequenceable';
import SoftDeleteable from 'radix/models/mixins/soft-deleteable';
import Timestampable  from 'radix/models/mixins/timestampable';

export default Model.extend(Sequenceable, SoftDeleteable, Timestampable, {
    alternateId : attr('string'),
    choiceType  : attr('string', { defaultValue: 'standard' }),
    description : attr('string'),
    name        : attr('string'),
    question    : belongsTo('question'),
});
