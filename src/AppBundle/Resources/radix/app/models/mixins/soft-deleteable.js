import DS from 'ember-data';
import Ember from 'ember';

export default Ember.Mixin.create({
    deleted: DS.attr('boolean', { defaultValue: false })
});
