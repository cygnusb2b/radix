import Ember from 'ember';
import Button from '../form-button';

export default Button.extend({

    model: null,
    field: null,

    label: 'Add',
    icon: 'ion-plus-round',

    classNames: ['btn-success'],

    layoutName: 'components/form-button',

    store: Ember.inject.service(),
    utility: Ember.inject.service('model-utility'),

    click: function() {
        let model = this.get('model');
        let meta  = this.get('utility').extractRelationshipMeta(model, this.get('field'));

        if (!meta) {
            throw new Error('No relationship found on the model.');
        }

        if (meta.options.inverse) {
            // Inverse relationship.
            let properties = {};
            properties[meta.options.inverse] = model;

            this.sendAction('preAdd', properties);
            this.get('store').createRecord(meta.type, properties);
            this.sendAction('postAdd', model);

        } else {
            throw new Error('Additional relationship adding NYI.');
        }
    }
});
