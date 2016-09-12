import Ember from 'ember';
import EmberSelectize from 'ember-cli-selectize/components/ember-selectize';

export default EmberSelectize.extend({

    modelName: null,

    optionValuePath: 'id',
    optionLabelPath: 'name',

    store: Ember.inject.service(),

    content: Ember.computed(function() {
        return this.get('store').findAll(this.get('modelName'), {reload: true});
    }),
});
