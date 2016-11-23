import Ember from 'ember';
import Transform from 'ember-data/transform';

const { A, typeOf } = Ember;

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
