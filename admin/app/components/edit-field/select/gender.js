import Ember from 'ember';
import EditFieldSelect from '../select';

export default EditFieldSelect.extend({

    layoutName: 'components/edit-field/select',

    options: Ember.computed(function() {
        return [
            { value : 'Unknown', label : 'Unknown' },
            { value : 'Male', label : 'Male' },
            { value : 'Female', label : 'Female' },
            { value : 'Non-Binary', label : 'Non-Binary' },
        ];
    }),
});
