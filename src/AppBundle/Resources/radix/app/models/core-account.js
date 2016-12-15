import Model         from 'ember-data/model';
import { hasMany }   from 'ember-data/relationships';
import Keyable       from 'radix/models/mixins/keyable';
import Timestampable from 'radix/models/mixins/timestampable';

export default Model.extend(Keyable, Timestampable, {
    applications : hasMany('core-application', { inverse: 'account' }),
});
