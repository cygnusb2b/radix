import DS from 'ember-data';
import Keyable from 'radix/models/mixins/keyable';
import Timestampable from 'radix/models/mixins/timestampable';

const { Model } = DS;

export default Model.extend(Keyable, Timestampable);
