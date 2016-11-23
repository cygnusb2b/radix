import DS from 'ember-data';
import Timestampable from 'radix/models/mixins/timestampable';

const { Model, attr, hasMany } = DS;

export default Model.extend(Timestampable, {
    givenName  : attr('string'),
    familyName : attr('string'),
    email      : attr('string'),
    lastLogin  : attr('date'),
    lastSeen   : attr('date'),
    logins     : attr('integer'),
    remembers  : attr('integer'),
    details    : hasMany('core-application-user', { inverse: 'user' }),
});
