import DS from 'ember-data';
import Identity from 'radix/models/identity';

const { attr } = DS;

export default Identity.extend({
    source     : attr('string'),
    identifier : attr('string')
});
