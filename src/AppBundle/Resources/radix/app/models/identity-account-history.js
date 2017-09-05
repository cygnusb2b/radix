import Fragment          from 'ember-data-model-fragments/fragment';
import attr              from 'ember-data/attr';

export default Fragment.extend({
    lastLogin : attr('date'),
    lastSeen  : attr('date'),
    logins    : attr('integer', { defaultValue: 0 }),
    remembers : attr('integer', { defaultValue: 0 }),
});
