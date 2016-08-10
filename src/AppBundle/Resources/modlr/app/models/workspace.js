import DS from 'ember-data';
import Keyable from 'modlr/models/mixins/keyable';

export default DS.Model.extend(Keyable, {
    models:   DS.hasMany('model', {
        inverse: 'workspace'
    }),
    segments: DS.hasMany('segment')
});
