import DS from 'ember-data';

const { Transform } = DS;

export default Transform.extend({
    deserialize(serialized) {
        return parseInt(serialized);
    },

    serialize(deserialized) {
        return parseInt(deserialized);
    }
});
