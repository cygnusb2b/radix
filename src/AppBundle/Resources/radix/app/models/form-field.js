import Model from 'ember-data/model';
import attr from 'ember-data/attr';
import Sequenceable from 'radix/models/mixins/sequenceable';
import SoftDeletable from 'radix/models/mixins/soft-deleteable';
import Timestampable from 'radix/models/mixins/timestampable';

export default Model.extend(Sequenceable, SoftDeletable, Timestampable, {
  required: attr('boolean', { defaultValue: false }),
  readonly: attr('boolean', { defaultValue: false }),
});
