import Ember from 'ember';
import attr  from 'ember-data/attr';

const { Mixin } = Ember;

export default Mixin.create({
    description : attr('string'),
    isPrimary   : attr('boolean', { defaultValue: false }),
    value       : attr('string'),
    emailType   : attr('string'),
});
