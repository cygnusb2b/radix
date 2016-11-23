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
        // Triggering a primary email response this way causes all email models to load at once, which causes huge overhead.
        // Should re-consider how to display primary email address (if needed at all) from a list view.
        let primary = null;
        return primary;
    }),
});
