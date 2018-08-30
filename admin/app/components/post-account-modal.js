import Ember from 'ember';
import ComponentQueryManager from 'ember-apollo-client/mixins/component-query-manager';
import ActionMixin from 'radix/mixins/action-mixin';
import updateIdentity from 'radix/gql/mutations/identity/update';
import banIdentity from 'radix/gql/mutations/identity/ban';
import unbanIdentity from 'radix/gql/mutations/identity/unban';


const { computed, Component } = Ember;

export default Component.extend(ComponentQueryManager, ActionMixin, {
  model: null,

  picture: computed.oneWay('model.picture'),
  displayName: computed.oneWay('model.displayName'),
  givenName: computed.oneWay('model.givenName'),
  familyName: computed.oneWay('model.familyName'),

  isOpen: false,
  isSubmitDisabled: computed.bool('isActionRunning'),

  actions: {
    async updateIdentity() {
      this.startAction();
      const mutation = updateIdentity;
      const id = this.get('model.id');
      const { givenName, familyName, picture, displayName } = this.getProperties('givenName,familyName,picture,displayName');
      const input = { id, givenName, familyName, picture, displayName };
      const variables = { input };
      try {
        await this.get('apollo').mutate({ mutation, variables }, 'updateIdentity');
        this.set('isOpen', false);
        this.get('notify').info('Identity updated.');
      } catch (e) {
        this.get('graphErrors').show(e)
      } finally {
        this.endAction();
      }
    },

    async banIdentity() {
      this.startAction();
      const mutation = banIdentity;
      const id = this.get('model.id');
      const variables = { input: { id } };
      try {
        await this.get('apollo').mutate({ mutation, variables }, 'banIdentity');
        this.set('isOpen', false);
        this.get('notify').info('Identity banned.');
      } catch (e) {
        this.get('graphErrors').show(e)
      } finally {
        this.endAction();
      }
    },

    async unbanIdentity() {
      this.startAction();
      const mutation = unbanIdentity;
      const id = this.get('model.id');
      const variables = { input: { id } };
      try {
        await this.get('apollo').mutate({ mutation, variables }, 'unbanIdentity');
        this.set('isOpen', false);
        this.get('notify').info('Identity unbanned.');
      } catch (e) {
        this.get('graphErrors').show(e)
      } finally {
        this.endAction();
      }
    },

    clear() {
    },
  },

});
