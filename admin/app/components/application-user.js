import Ember from 'ember';

const { computed, typeOf, Component, inject: { service } } = Ember;

export default Component.extend({
  tagName: 'tr',

  model: null,
  editing: false,

  _roles: ['ROLE_SUPERADMIN', 'ROLE_USER'],
  roles: computed('roles.[]', 'model.roles.[]', function() {
    return this.get('_roles').concat(this.get('model.roles').toArray()).uniq();
  }),

  isEditing: computed('model.{isNew,isDirty}', 'editing', function() {
    if (this.get('model.isNew') || this.get('model.isDirty')) return true;
    return this.get('editing');
  }),

  canSave: computed('model.{user,application}', function() {
    if (!this.get('model.application.id') || !this.get('model.user.id')) return false;
    return true;
  }),

  actions: {
    toggleEdit: function() {
      this.set('editing', !this.get('editing'));
    },
    save: function() {
      this.get('model').save().then(function() {
        this.sendAction('onUpdate');
        this.set('editing', false);
      }.bind(this));
    },
    reset: function() {
      const model = this.get('model');
      if (model.get('isNew')) {
        this.sendAction('cancelNew', model);
      } else {
        model.rollbackAttributes();
        model.reload();
      }
      this.set('editing', false);
    },

    confirmDelete: function() {
      if (confirm('Are you sure you want to delete this record?')) {
        this.get('model').destroyRecord();
      }
    }

  },

});
