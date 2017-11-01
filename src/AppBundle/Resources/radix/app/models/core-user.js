import Model         from 'ember-data/model';
import attr          from 'ember-data/attr';
import { hasMany }   from 'ember-data/relationships';
import Timestampable from 'radix/models/mixins/timestampable';
import { array } from 'ember-data-model-fragments/attributes';

export default Model.extend(Timestampable, {
    roles      : array('string', { defaultValue: ['ROLE_CORE\\USER'] }),
    givenName  : attr('string'),
    familyName : attr('string'),
    email      : attr('string'),
    lastLogin  : attr('date'),
    lastSeen   : attr('date'),
    logins     : attr('integer'),
    remembers  : attr('integer'),
    details    : hasMany('core-application-user', { inverse: 'user' }),
});
