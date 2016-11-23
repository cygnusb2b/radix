import DS from 'ember-data';
import Keyable from 'radix/models/mixins/keyable';
import Timestampable from 'radix/models/mixins/timestampable';

const { Model, attr, belongsTo, hasMany } = DS;

export default Model.extend(Keyable, Timestampable, {
    publicKey      : attr('string'),
    allowedOrigins : attr('array'),
    account        : belongsTo('core-account'),
    users          : hasMany('core-application-user', { inverse: 'user' }),
});
