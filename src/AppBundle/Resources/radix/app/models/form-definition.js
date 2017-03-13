import Model                  from 'ember-data/model';
import attr                   from 'ember-data/attr';
// import { hasMany, belongsTo } from 'ember-data/relationships';
import Keyable                from 'radix/models/mixins/keyable';
import Timestampable          from 'radix/models/mixins/timestampable';

export default Model.extend(Keyable, Timestampable, {
});
