import DS from 'ember-data';
import Fieldable from 'modlr/models/mixins/fieldable';

export default DS.Model.extend(Fieldable, {
    inverseField: DS.attr('string'),
    relatedModel: DS.belongsTo('model'),
    relType: DS.attr('string')
});
