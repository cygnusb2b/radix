import DS from 'ember-data';
import Keyable from 'radix/models/mixins/keyable';
import SoftDeleteable from 'radix/models/mixins/soft-deleteable';
import Timestampable from 'radix/models/mixins/timestampable';

const { Model, attr, hasMany } = DS;

export default Model.extend(Keyable, SoftDeleteable, Timestampable, {
    allowHtml    : attr('boolean', { defaultValue: false }),
    choices      : hasMany('question-choice', { inverse: 'question' }),
    label        : attr('string'),
    questionType : attr('string',  { defaultValue: 'text' }),
    tags         : hasMany('question-tag'),
});
