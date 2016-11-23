import Model         from 'ember-data/model';
import attr          from 'ember-data/attr';
import { belongsTo } from 'ember-data/relationships';
import Timestampable from 'radix/models/mixins/timestampable';

export default Model.extend(Timestampable, {
    name      : attr('string'),
    isPrimary : attr('boolean', { defaultValue: false }),
    value     : attr('string'),
    account   : belongsTo('identity-account'),
});
