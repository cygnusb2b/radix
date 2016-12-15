import Integration from 'radix/models/integration';
import attr        from 'ember-data/attr';
import { hasMany } from 'ember-data/relationships';

export default Integration.extend({
    identifier : attr('string'),
    boundTo    : attr('string', { defaultValue: 'identity' }),
    tagWith    : hasMany('question-tag'),
});
