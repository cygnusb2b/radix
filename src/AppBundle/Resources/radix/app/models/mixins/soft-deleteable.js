import DS from 'ember-data';
import Ember from 'ember';

const { Mixin } = Ember;
const { attr } = DS;

export default Mixin.create({
    deleted : attr('boolean', { defaultValue: false }),
});
