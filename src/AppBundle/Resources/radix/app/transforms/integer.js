import DS from 'ember-data';

const { Transform } = DS;

export default Transform.extend({
    deserialize(serialized) {
        if (null === serialized) {
            return;
        }
        return parseInt(serialized);
    },

    serialize(deserialized) {
        if (null === deserialized) {
            return;
        }
        return parseInt(deserialized);
    }
});
