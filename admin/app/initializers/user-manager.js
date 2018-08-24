export function initialize(appInstance) {
  appInstance.inject('controller', 'user-manager', 'service:user-manager');
  appInstance.inject('route', 'user-manager', 'service:user-manager');
  appInstance.inject('component', 'user-manager', 'service:user-manager');
}

export default {
  name: 'user-manager',
  initialize: initialize
};