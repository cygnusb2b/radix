import DS from 'ember-data';
import Timestampable from 'radix/models/mixins/timestampable';

export default DS.Model.extend(Timestampable, {
    name:        DS.attr('string'),
    description: DS.attr('string'),
    sequence:    DS.attr('number', { defaultValue: 0 }),
    demographic: DS.belongsTo('demographic'),
    mappings:    DS.hasMany('demographic-mapping', { inverse: 'owner' }),
    isNone:      DS.attr('boolean', { defaultValue: false }),
    isOther:     DS.attr('boolean', { defaultValue: false }),
    isHidden:    DS.attr('boolean', { defaultValue: false }),
});
