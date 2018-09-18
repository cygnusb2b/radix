import Ember from 'ember';
import ComponentQueryManager from 'ember-apollo-client/mixins/component-query-manager';
import ActionMixin from 'radix/mixins/action-mixin';
import changeUserProfile from 'radix/gql/mutations/change-user-profile';

const { computed, Component } = Ember;

export default Component.extend(ComponentQueryManager, ActionMixin, {
  model: null,

  givenName: computed.oneWay('model.givenName'),
  familyName: computed.oneWay('model.familyName'),

  isOpen: false,
  canChange: computed('reasonForPreventChange', function() {
    return (!this.get('reasonForPreventChange')) ? true : false;
  }),

  isSubmitDisabled: computed('canChange', 'isActionRunning', function() {
    if (this.get('isActionRunning')) return true;
    if (this.get('canChange')) return false;
    return true;
  }),

  reasonForPreventChange: computed('givenName', 'familyName', function() {
    if (!this.get('givenName.length') || this.get('familyName.length') < 6) {
      return 'supply a name of at least two characers.';
    }
    return null;
  }),

  actions: {
    async changeProfile() {
      this.startAction();
      const mutation = changeUserProfile;
      const id = this.get('model.id');
      const { givenName, familyName } = this.get('user');
      const input = { id, givenName, familyName };
      const variables = { input };
      try {
        await this.get('apollo').mutate({ mutation, variables }, 'changeUserProfile');
        this.set('isOpen', false);
        this.get('notify').info('Profile successfully updated.');
      } catch (e) {
        this.get('graphErrors').show(e)
      } finally {
        this.endAction();
      }
    },
    async clearProfile() {

    }
  },

});
