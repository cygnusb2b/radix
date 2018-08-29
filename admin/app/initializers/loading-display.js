const name = 'loadingDisplay';

export function initialize(application) {
  application.inject('controller', name, `service:${name}`);
}

export default {
  name,
  initialize,
};
