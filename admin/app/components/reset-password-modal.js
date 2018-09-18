import Component from '@ember/component';
import { computed } from '@ember/object';
import { inject } from '@ember/service';
import ComponentQueryManager from 'ember-apollo-client/mixins/component-query-manager';
import ActionMixin from 'radix/mixins/action-mixin';

import mutation from 'radix/gql/mutations/reset-user-password';

export default Component.extend(ComponentQueryManager, ActionMixin, {
  session: inject(),

  password: '',
  confirm: '',

  email: null,
  token: null,

  showPassword: false,
  isOpen: true,
  reset: false,

  canChange: computed('reasonForPreventChange', function() {
    return (!this.get('reasonForPreventChange')) ? true : false;
  }),

  isSubmitDisabled: computed('canChange', 'isActionRunning', function() {
    if (this.get('isActionRunning')) return true;
    if (this.get('canChange')) return false;
    return true;
  }),

  reasonForPreventChange: computed('password', 'showPassword', 'confirm', function() {
    if (!this.get('password').length || this.get('password').length < 6) {
      return 'supply a new password of at least six characters.';
    }
    if (this.get('showPassword')) return null;
    if (this.get('password') === this.get('confirm')) return null;
    return 'please confirm your password with the same value.';
  }),

  didInsertElement() {
    this.set('password', '');
    this.set('confirm', '');
    this.set('showPassword', false);
  },

  actions: {
    async changePassword() {
      this.startAction();
      const value = this.get('password');
      const token = this.get('token');
      const confirm = this.get('showPassword') ? value : this.get('confirm');
      const variables = { input: { token, value, confirm } };
      try {
        await this.get('apollo').mutate({ mutation, variables }, 'resetUserPassword');
        this.set('reset', true);
        this.set('isOpen', false);
        this.get('notify').info('Password successfully reset, you may now login.');
      } catch (e) {
        this.get('graphErrors').show(e)
      } finally {
        this.endAction();
      }
    },

    clearPassword() {
      this.set('password', '');
      this.set('confirm', '');
      this.set('showPassword', false);
      if (!this.get('reset')) setTimeout(() => this.set('isOpen', true), 100);
    },

    toggleShowPassword() {
      this.set('showPassword', !this.get('showPassword'));
    }
  },

});
