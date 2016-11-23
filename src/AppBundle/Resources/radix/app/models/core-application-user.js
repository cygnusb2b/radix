import Model         from 'ember-data/model';
import attr          from 'ember-data/attr';
import { belongsTo } from 'ember-data/relationships';
import Timestampable from 'radix/models/mixins/timestampable';

export default Model.extend(Timestampable, {
    roles       : attr('array'),
    user        : belongsTo('core-user'),
    application : belongsTo('core-application'),
});
