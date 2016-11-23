import Ember               from 'ember';
import Identity            from 'radix/models/identity';
import attr                from 'ember-data/attr';
import { hasMany }         from 'ember-data/relationships';
import { fragment, array } from 'model-fragments/attributes';
import Integrateable       from 'radix/models/mixins/integrateable';

const { computed } = Ember;

export default Identity.extend(Integrateable, {
    displayName  : attr('string'),
    picture      : attr('string'),
    roles        : array(),
    credentials  : fragment('identity-account-credentials'),
    settings     : fragment('identity-account-settings'),
    history      : fragment('identity-account-history'),
    emails       : hasMany('identity-account-email', { inverse: 'account' }),
    primaryEmail : computed('emails.[]', function() {

        let primary = null;

        // Try verified emails first.
        this.get('emails').forEach(function(email) {
            let verification = email.get('verification');
            if (!verification || !verification.get('verified')) {
                return;
            }
            if (!primary || email.get('isPrimary')) {
                primary = email.get('value');
            }
        });

        if (primary) {
            return primary;
        }

        // Try again with non-verified.
        this.get('emails').forEach(function(email) {
            if (!primary || email.get('isPrimary')) {
                primary = email.get('value');
            }
        });

        return primary;
    }),
});
