import DS from 'ember-data';
import Identity from 'radix/models/identity';

const { attr, hasMany } = DS;

export default Identity.extend({
    displayName : attr('string'),
    picture     : attr('string'),
    roles       : attr(),
    emails      : hasMany('identity-account-email', { inverse: 'account' }),
});
