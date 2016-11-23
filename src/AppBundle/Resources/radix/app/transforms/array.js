import Ember from 'ember';
import DS from 'ember-data';

const { Transform } = DS;
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
