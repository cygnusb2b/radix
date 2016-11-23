import DS from 'ember-data';
import Timestampable from 'radix/models/mixins/timestampable';

const { Model, attr } = DS;

export default Model.extend(Timestampable, {
    name    : attr('string'),
    client  : attr('string'),
    brand   : attr('string'),
    appid   : attr('string'),
    inputid : attr('string'),
});
