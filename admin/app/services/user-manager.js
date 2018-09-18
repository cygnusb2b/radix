import Ember from 'ember';
import Permissions from 'radix/objects/permissions';

const { inject: { service }, isEmpty, RSVP: { Promise }, Service, computed, get } = Ember;

export default Service.extend({
  session : service('session'),
  store   : service(),
  user    : computed.reads('session.data.authenticated'),

  load() {
    if (!this.get('applicationId') && this.get('session').isAuthenticated) {
      this.get('session').invalidate();
      return Promise.reject('No application ID found!');
    }
    const appId = this.get('session.data.application._id');
    return Promise.resolve();
  },

  getSession() {
    return this.get('session');
  },

  application: computed('session.data.{application,authenticated.applications.firstObject}', function() {
    if (!this.get('session.data.application')) {
      this.set('session.data.application', this.get('session.data.authenticated.applications.firstObject'));
    }
    return this.get('session.data.application');
  }),

  applicationId: computed.reads('application._id'),
  applicationKey: computed.reads('application.id'),

  permissions: computed('user.id', 'user.roles.[]', 'applicationKey', function() {
    const userId = this.get('user.id');
    const permissions = new Permissions();
    if (isEmpty(userId)) {
      return;
    }
    const defaultRole = 'ROLE_CORE\\USER';
    const roles = this.get('session.data.authenticated.roles');
    const currentAppKey = this.get('applicationKey');
    const role = (roles && get(roles, 'firstObject')) ? get(roles, 'firstObject') : defaultRole;
    const superAdminRole = `ROLE_${currentAppKey}\\SUPERADMIN`.toUpperCase();
    const adminRole = `ROLE_${currentAppKey}\\ADMIN`.toUpperCase();

    if (-1 !== roles.indexOf(superAdminRole)) {
      permissions.fullAccess();
    } else {
      if (-1 !== roles.indexOf(adminRole)) {
        permissions.set('users', { all: true });
        permissions.set('applications', { create: false, edit: true, list: false });
      }
      permissions.set('comments', { create: true, edit: true, list: true });
    }

    return permissions;
  }),
});
