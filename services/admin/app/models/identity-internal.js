import Identity                from 'radix/models/identity';
import IdentityEmailEmbeddable from 'radix/models/mixins/identity-email-embeddable';
import Integrateable           from 'radix/models/mixins/integrateable';

export default Identity.extend(IdentityEmailEmbeddable, Integrateable, {

});
