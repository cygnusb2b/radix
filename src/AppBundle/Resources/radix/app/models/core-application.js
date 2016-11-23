import Model                  from 'ember-data/model';
import attr                   from 'ember-data/attr';
import { belongsTo, hasMany } from 'ember-data/relationships';
import Keyable                from 'radix/models/mixins/keyable';
import Timestampable          from 'radix/models/mixins/timestampable';

export default Model.extend(Keyable, Timestampable, {
    publicKey      : attr('string'),
    allowedOrigins : attr('array'),
    account        : belongsTo('core-account'),
    users          : hasMany('core-application-user', { inverse: 'user' }),
});
