import Identity from 'radix/models/identity';
import attr     from 'ember-data/attr';

export default Identity.extend({
    source     : attr('string'),
    identifier : attr('string')
});
