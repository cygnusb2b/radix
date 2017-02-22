import Model         from 'ember-data/model';
import { array }     from 'ember-data-model-fragments/attributes';
import { belongsTo } from 'ember-data/relationships';
import Timestampable from 'radix/models/mixins/timestampable';

export default Model.extend(Timestampable, {
    roles       : array(),
    user        : belongsTo('core-user'),
    application : belongsTo('core-application'),
});
