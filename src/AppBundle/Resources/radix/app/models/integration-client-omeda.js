import Model         from 'ember-data/model';
import attr          from 'ember-data/attr';
import Timestampable from 'radix/models/mixins/timestampable';

export default Model.extend(Timestampable, {
    name    : attr('string'),
    client  : attr('string'),
    brand   : attr('string'),
    appid   : attr('string'),
    inputid : attr('string'),
});
