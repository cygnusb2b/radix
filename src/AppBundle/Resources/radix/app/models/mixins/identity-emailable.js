import DS from 'ember-data';
import Ember from 'ember';

const { Mixin } = Ember;
const { attr } = DS;

export default Mixin.create({
    description : attr('string'),
    isPrimary   : attr('boolean', { defaultValue: false }),
    value       : attr('string'),
    emailType   : attr('string'),
});
