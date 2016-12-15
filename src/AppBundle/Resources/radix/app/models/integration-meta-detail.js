import Fragment          from 'model-fragments/fragment';
import attr              from 'ember-data/attr';

export default Fragment.extend({
    integrationId : attr('string'),
    identifier    : attr('string'),
    firstRunDate  : attr('date'),
    lastRunDate   : attr('date'),
    timesRan      : attr('integer', { defaultValue: 0 }),
});
