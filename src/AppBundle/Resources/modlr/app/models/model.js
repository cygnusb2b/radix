import DS from 'ember-data';
import Keyable from 'modlr/models/mixins/keyable';
import Timestampable from 'modlr/models/mixins/timestampable';

export default DS.Model.extend(Keyable, Timestampable, {
    attributes:     DS.hasMany('attribute'),
    description:    DS.attr('string'),
    embeds:         DS.hasMany('embed'),
    mixins:         DS.hasMany('mixin'),
    relationships:  DS.hasMany('relationship'),
    workspace:      DS.belongsTo('workspace')
});
