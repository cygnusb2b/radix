import DS from 'ember-data';
import Fieldable from 'modlr/models/mixins/fieldable';

export default DS.Model.extend(Fieldable, {
    dataType: DS.attr('string')
});
