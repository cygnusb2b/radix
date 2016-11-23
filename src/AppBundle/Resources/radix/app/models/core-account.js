import DS from 'ember-data';
import Keyable from 'radix/models/mixins/keyable';
import Timestampable from 'radix/models/mixins/timestampable';

const { Model, hasMany } = DS;

export default Model.extend(Keyable, Timestampable, {
    applications : hasMany('core-application', { inverse: 'account' }),
});
