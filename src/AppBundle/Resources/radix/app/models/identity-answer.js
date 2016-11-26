import Model              from 'ember-data/model';
import { belongsTo }      from 'ember-data/relationships';
import Timestampable      from 'radix/models/mixins/timestampable';
import QuestionAnswerable from 'radix/models/mixins/question-answerable';

export default Model.extend(Timestampable, QuestionAnswerable, {
    identity : belongsTo('identity', { polymorphic: true })
});
