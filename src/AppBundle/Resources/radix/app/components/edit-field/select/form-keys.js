import Ember from 'ember';
import EditFieldSelect from '../select';
import config from '../../../config/environment';

export default EditFieldSelect.extend({

    layoutName: 'components/edit-field/select',

    options: Ember.computed(function() {
        return config.formKeys.sortBy('label');
    }),
});
