import Ember        from 'ember';
import { fragment } from 'ember-data-model-fragments/attributes';

const { Mixin } = Ember;

export default Mixin.create({
    integration : fragment('integration-meta'),
});
