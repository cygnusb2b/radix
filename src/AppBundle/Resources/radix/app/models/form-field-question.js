import FormField from 'radix/models/form-field';
import { belongsTo } from 'ember-data/relationships';

export default FormField.extend({
  question: belongsTo('question'),
  form: belongsTo('form-definition'),
});
