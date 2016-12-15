import Model         from 'ember-data/model';
import attr          from 'ember-data/attr';
import { hasMany }   from 'ember-data/relationships';
import Keyable       from 'radix/models/mixins/keyable';
import Sequenceable  from 'radix/models/mixins/sequenceable';
import Timestampable from 'radix/models/mixins/timestampable';

export default Model.extend(Keyable, Sequenceable, Timestampable, {
    description : attr('string'),
    tags        : hasMany('product-tag'),
});
