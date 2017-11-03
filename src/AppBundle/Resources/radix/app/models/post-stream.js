import Model                    from 'ember-data/model';
import attr                     from 'ember-data/attr';
import SoftDeleteable           from 'radix/models/mixins/soft-deleteable';
import Timestampable            from 'radix/models/mixins/timestampable';
import { belongsTo, hasMany }   from 'ember-data/relationships';

export default Model.extend(SoftDeleteable, Timestampable, {
  title         : attr('string'),
  url           : attr('string'),
  identifier    : attr('string'),
  active        : attr('boolean', { defaultValue: true }),
  post          : hasMany('post', {inverse: 'stream'})
});