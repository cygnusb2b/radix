import Component from '@ember/component';
import { computed } from '@ember/object';
import ComponentQueryManager from 'ember-apollo-client/mixins/component-query-manager';
import ActionMixin from 'radix/mixins/action-mixin';
import mutation from 'radix/gql/mutations/core-application-user/add';

export default Component.extend(ComponentQueryManager, ActionMixin, {
  isOpen: false,
  isSubmitDisabled: computed.bool('isActionRunning'),

  id: null,

  email: '',
  givenName: '',
  familyName: '',
  role: 'ROLE_USER',
  roles: computed('role', function() {
    return [this.get('role')];
  }),

  choices: [ 'ROLE_USER', 'ROLE_ADMIN' ],

  actions: {
    async select({ email, givenName, familyName }) {
      this.setProperties({ email, givenName, familyName });
    },

    async submit() {
      this.startAction();
      const props = this.getProperties('email','givenName','familyName','roles');
      let valid = true;
      Object.keys(props).forEach((prop) => {
        if (!props[prop] && valid) {
          this.get('notify').warning('All fields are required.');
          valid = false;
        }
      });
      if (!valid) {
        this.endAction();
        return;
      }
      const id = this.get('id');
      const variables = { input: { id, payload: { ...props } } };
      try {
        await this.get('apollo').mutate({ mutation, variables });
        if (!this.isDestroyed) this.set('isOpen', false);
        this.get('notify').info('User invited.');
        this.sendAction('onComplete');
      } catch (e) {
        this.get('graphErrors').show(e)
      } finally {
        this.endAction();
      }
    },

    clear() {
      this.setProperties({
        email: '',
        givenName: '',
        familyName: '',
        role: 'ROLE_USER',
      });
    },
  },

});
