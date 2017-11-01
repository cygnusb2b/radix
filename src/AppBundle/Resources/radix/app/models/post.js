import Model             from 'ember-data/model';
import attr              from 'ember-data/attr';
import SoftDeleteable    from 'radix/models/mixins/soft-deleteable';
import Timestampable     from 'radix/models/mixins/timestampable';
import { belongsTo }     from 'ember-data/relationships';

export default Model.extend(SoftDeleteable, Timestampable, {
  body        : attr('string'),
  ipAddress   : attr('string'),
  anonymize   : attr('boolean'),
  displayName : attr('string'),
  picture     : attr('string'),
  banned      : attr('boolean', { defaultValue: false }),
  approved    : attr('boolean'),
  stream      : belongsTo('post-stream')
});