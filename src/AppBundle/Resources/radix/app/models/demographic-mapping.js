import DS from 'ember-data';
import Timestampable from 'radix/models/mixins/timestampable';

export default DS.Model.extend(Timestampable, {
    owner:       DS.belongsTo('demographic-choice'),
    demographic: DS.belongsTo('demographic'),
    choices:     DS.hasMany('demographic-choice'),
});
