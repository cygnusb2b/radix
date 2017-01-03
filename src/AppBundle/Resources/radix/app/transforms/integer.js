import Transform from 'ember-data/transform';

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
