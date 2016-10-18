import EmberSelectize from 'ember-cli-selectize/components/ember-selectize';

export default EmberSelectize.extend({

    optionValuePath: 'id',
    optionLabelPath: 'name',
    sortField: 'name',

});
