import Identity                from 'radix/models/identity';
import attr                    from 'ember-data/attr';
import IdentityEmailEmbeddable from 'radix/models/mixins/identity-email-embeddable';

export default Identity.extend(IdentityEmailEmbeddable, {
    source     : attr('string'),
    identifier : attr('string')
});
