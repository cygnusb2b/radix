import { A } from '@ember/array';
import { typeOf } from '@ember/utils'
import Transform from 'ember-data/transform';

export default Transform.extend({
  deserialize(serialized) {
    if ('array' === typeOf(serialized)) {
      return A(serialized);
    } else {
      return A();
    }
  },

  serialize(deserialized) {
    return deserialized.toArray();
  }
});
