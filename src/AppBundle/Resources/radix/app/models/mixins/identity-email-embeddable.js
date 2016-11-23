import Ember             from 'ember';
import { fragmentArray } from 'model-fragments/attributes';

const { Mixin, computed } = Ember;

export default Mixin.create({
    emails       : fragmentArray('identity-email'),
});
