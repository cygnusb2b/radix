import Ember from 'ember';
import Permissions from 'radix/objects/permissions';

const { inject: { service }, isEmpty, RSVP: { Promise }, Service, computed, get } = Ember;

export default Service.extend({
  session : service('session'),
  store   : service(),
  user    : null,

  load() {
    return new Promise((resolve, reject) => {
      let userId = this.get('session.data.authenticated.id');
      if (!isEmpty(userId)) {
        this.get('store').find('core-user', userId).then((user) => {
          this.set('user', user);
          resolve();
        }, reject);
      } else {
        resolve();
      }
    });
  },

  getSession() {
    return this.get('session');
  },

  applicationId: computed.reads('session.data.application.id'),

  permissions: computed('user.id', 'user.roles.[]', 'applicationId', function() {
    const userId = this.get('user.id');
    const permissions = Permissions.create();
    if (isEmpty(userId)) {
      return permissions;
    }
    const defaultRole = 'ROLE_CORE\\USER';
    const roles = this.get('session.data.authenticated.roles');
    const currentAppId = this.get('applicationId');
    const role = (roles && get(roles, 'firstObject')) ? get(roles, 'firstObject') : defaultRole;
    const checkRole = `ROLE_${currentAppId}\\SUPERADMIN`.toUpperCase();

    if (-1 !== roles.indexOf(checkRole)){
      permissions.fullAccess();
    } else {
      permissions.set('comments', { create: true, edit: true, list: true });
    }

    return permissions;
  }),
});