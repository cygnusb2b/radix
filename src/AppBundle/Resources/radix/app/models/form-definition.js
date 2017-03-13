import Model from 'ember-data/model';
import attr from 'ember-data/attr';
import { hasMany } from 'ember-data/relationships';
import Keyable from 'radix/models/mixins/keyable';
import Timestampable from 'radix/models/mixins/timestampable';

export default Model.extend(Keyable, Timestampable, {
  title: attr('string'),
  description: attr('string'),
  identityFields: hasMany('form-field-identity', { inverse: 'form' }),
  questionFields: hasMany('form-field-question', { inverse: 'form' }),
});
