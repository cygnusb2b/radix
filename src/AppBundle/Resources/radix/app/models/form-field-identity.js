import FormField from 'radix/models/form-field';
import attr from 'ember-data/attr';
import { belongsTo } from 'ember-data/relationships';

export default FormField.extend({
  key: attr('string'),
  label: attr('string'),
  form: belongsTo('form-definition'),
});
