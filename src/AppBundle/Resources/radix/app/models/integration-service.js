import Model         from 'ember-data/model';
import attr          from 'ember-data/attr';
import Timestampable from 'radix/models/mixins/timestampable';

export default Model.extend(Timestampable, {
    name : attr('string'),
});
