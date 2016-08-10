import DS from 'ember-data';
import Ember from 'ember';

export default Ember.Mixin.create({
    createdDate: DS.attr('date'),
    touchedDate: DS.attr('date'),
    updatedDate: DS.attr('date')
});
