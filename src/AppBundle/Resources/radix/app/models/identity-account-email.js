import DS from 'ember-data';
import Timestampable from 'radix/models/mixins/timestampable';

const { Model, attr, belongsTo } = DS;

export default Model.extend(Timestampable, {
    name      : attr('string'),
    isPrimary : attr('boolean', { defaultValue: false }),
    value     : attr('string'),
    account   : belongsTo('identity-account'),
});
