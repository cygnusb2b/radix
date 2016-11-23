import Identity    from 'radix/models/identity';
import attr        from 'ember-data/attr';
import { hasMany } from 'ember-data/relationships';

export default Identity.extend({
    displayName : attr('string'),
    picture     : attr('string'),
    roles       : attr(),
    emails      : hasMany('identity-account-email', { inverse: 'account' }),
});
