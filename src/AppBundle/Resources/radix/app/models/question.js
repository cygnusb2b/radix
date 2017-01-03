import Model                  from 'ember-data/model';
import attr                   from 'ember-data/attr';
import { hasMany, belongsTo } from 'ember-data/relationships';
import Keyable                from 'radix/models/mixins/keyable';
import SoftDeleteable         from 'radix/models/mixins/soft-deleteable';
import Timestampable          from 'radix/models/mixins/timestampable';

export default Model.extend(Keyable, SoftDeleteable, Timestampable, {
    allowHtml      : attr('boolean', { defaultValue: false }),
    boundTo        : attr('string', { defaultValue: 'submission' }),
    label          : attr('string'),
    questionType   : attr('string',  { defaultValue: 'text' }),
    choices        : hasMany('question-choice', { inverse: 'question' }),
    relatedChoices : hasMany('question-choice', { inverse: null }),
    pull           : belongsTo('integration-question-pull'),
    tags           : hasMany('question-tag'),
});
