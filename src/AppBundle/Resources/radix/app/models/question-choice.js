import Ember                   from 'ember';
import Model                   from 'ember-data/model';
import attr                    from 'ember-data/attr';
import { belongsTo, hasMany }  from 'ember-data/relationships';
import Integrateable           from 'radix/models/mixins/integrateable';
import Sequenceable            from 'radix/models/mixins/sequenceable';
import SoftDeleteable          from 'radix/models/mixins/soft-deleteable';
import Timestampable           from 'radix/models/mixins/timestampable';

const { computed } = Ember;

export default Model.extend(Integrateable, Sequenceable, SoftDeleteable, Timestampable, {
    alternateId : attr('string'),
    choiceType  : attr('string', { defaultValue: 'standard' }),
    description : attr('string'),
    fullName    : attr('string'),
    name        : attr('string'),
    question    : belongsTo('question'),
    dependents  : hasMany('question-choice', { inverse: null }),

    hasDependents : computed('dependents.[]', function() {
      return this.get('dependents.length') > 0;
    }),
});
