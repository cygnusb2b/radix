import Model         from 'ember-data/model';
import attr          from 'ember-data/attr';
import { belongsTo } from 'ember-data/relationships';
import Timestampable from 'radix/models/mixins/timestampable';

export default Model.extend(Timestampable, {
    name         : attr('string'),
    description  : attr('string'),
    enabled      : attr('boolean', { defaultValue: true }),
    extra        : attr(),
    firstRunDate : attr('date'),
    lastRunDate  : attr('date'),
    timesRan     : attr('integer', { defaultValue: 0 }),
    service      : belongsTo('integration-service', { polymorphic: true }),
});
