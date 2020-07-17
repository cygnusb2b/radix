import Ember from 'ember';
import RelManyItem from './item';

const {
    computed,
    observer
} = Ember;

export default RelManyItem.extend({

    layoutName: 'components/edit-field/rel-many/item',

    demographics: computed('model.owner.demographic.id', function() {
        let criteria = JSON.stringify({ id: { $ne: this.get('model.owner.demographic.id') } });

        return this.get('store').query('demographic', {
            filter: { query: { criteria: criteria } }
        });
    }),

    demographic: null,

    isDemographicSelected: computed('model.demographic.id', function() {
        return (this.get('model.demographic.id')) ? true : false;
    }),

    demographicDidChange: observer('model.demographic.id', function() {
        let demographic = this.get('model.demographic');
        let choices     = [];

        if (!demographic.get('id')) {
            this.get('model.choices').forEach(function(choice) {
                this.get('model.choices').removeObject(choice);
            }, this);
            this.set('demographicChoices', choices);
        } else {
            let criteria = JSON.stringify({ demographic: demographic.get('id') });
            let promise  = this.get('store').query('demographic-choice', {
                filter: { query: { criteria: criteria } }
            });

            this.set('demographicChoices', promise);
        }
    }),

    demographicChoices: [],

    canSave: Ember.computed('model.choices.[]', 'model.demographic.id', function() {
        let demographic = this.get('model.demographic');
        return (demographic && this.get('model.choices.length') > 0);
    }),

    confirmValue: Ember.computed('confirmField', function() {
        return 'Mapping To: ' + this.get('model.owner.value') + ' (' + this.get('model.owner.demographic.name') + ')';
    }),

    store: Ember.inject.service(),
    routing: Ember.inject.service('-routing'),

    actions: {

        setDemographic: function(demographic) {
            this.set('model.demographic', demographic);
        },

        addDemographicChoice: function(demographicChoice) {
            this.get('model.choices').pushObject(demographicChoice);
        },

        removeDemographicChoice: function(demographicChoice) {
            this.get('model.choices').removeObject(demographicChoice);
        },
    }
});
