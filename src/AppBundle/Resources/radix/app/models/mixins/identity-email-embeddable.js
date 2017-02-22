import Ember             from 'ember';
import { fragmentArray } from 'ember-data-model-fragments/attributes';

const { Mixin, computed } = Ember;

export default Mixin.create({
    emails       : fragmentArray('identity-email'),
    primaryEmail : computed('emails.[]', function() {
        let primary = null;
        this.get('emails').forEach(function(email) {
            if (null === primary) {
                // Use first email as primary, as a default.
                primary = email.get('value');
            }
            if (email.get('isPrimary')) {
                primary = email.get('value');
            }
        });
        return primary;
    }),
});
