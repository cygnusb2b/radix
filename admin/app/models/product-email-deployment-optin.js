import Model         from 'ember-data/model';
import attr          from 'ember-data/attr';
import { belongsTo } from 'ember-data/relationships';
import Timestampable from 'radix/models/mixins/timestampable';

export default Model.extend(Timestampable, {
    email   : attr('string'),
    optedIn : attr('boolean', { defaultValue: false }),
    product : belongsTo('product-email-deployment'),
});
