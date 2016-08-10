import DS from 'ember-data';
import Ember from 'ember';

export default Ember.Mixin.create({
    description: DS.attr('string'),
    key:         DS.attr('string'),
    modelOwner:  DS.attr()
});
