import DS from 'ember-data';
import Timestampable from 'radix/models/mixins/timestampable';

export default DS.Model.extend(Timestampable, {
    name:    DS.attr('string'),
    client:  DS.attr('string'),
    brand:   DS.attr('string'),
    appid:   DS.attr('string'),
    inputid: DS.attr('string'),
});
