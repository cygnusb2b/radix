import Ember from 'ember';
import Resolver from './resolver';
import loadInitializers from 'ember-load-initializers';
import config from './config/environment';

let App;

Ember.MODEL_FACTORY_INJECTIONS = true;

App = Ember.Application.extend({
  modulePrefix: config.modulePrefix,
  rootElement: '#radix',
  podModulePrefix: config.podModulePrefix,
  LOG_TRANSITIONS: config.LOG_TRANSITIONS,
  LOG_TRANSITIONS_INTERNAL: config.LOG_TRANSITIONS_INTERNAL,
  Resolver
});

loadInitializers(App, config.modulePrefix);

export default App;
