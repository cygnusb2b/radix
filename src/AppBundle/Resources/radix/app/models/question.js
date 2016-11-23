import Model          from 'ember-data/model';
import attr           from 'ember-data/attr';
import { hasMany }    from 'ember-data/relationships';
import Keyable        from 'radix/models/mixins/keyable';
import SoftDeleteable from 'radix/models/mixins/soft-deleteable';
import Timestampable  from 'radix/models/mixins/timestampable';

export default Model.extend(Keyable, SoftDeleteable, Timestampable, {
    allowHtml    : attr('boolean', { defaultValue: false }),
    choices      : hasMany('question-choice', { inverse: 'question' }),
    label        : attr('string'),
    questionType : attr('string',  { defaultValue: 'text' }),
    tags         : hasMany('question-tag'),
});
