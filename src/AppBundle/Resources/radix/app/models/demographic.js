import DS from 'ember-data';
import Timestampable from 'radix/models/mixins/timestampable';

export default DS.Model.extend(Timestampable, {
    name:             DS.attr('string'),
    label:            DS.attr('string'),
    choices:          DS.hasMany('demographic-choice', { inverse: 'demographic' }),
    labels:           DS.hasMany('demographic-label'),
    answerType:       DS.attr('string',  { defaultValue: 'string' }),
    allowHtml:        DS.attr('boolean', { defaultValue: false }),
});
