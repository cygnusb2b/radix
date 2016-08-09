import Ember from 'ember';
import Resolver from 'ember/resolver';
import loadInitializers from 'ember/load-initializers';
import config from './config/environment';

let App;

Ember.MODEL_FACTORY_INJECTIONS = true;

App = Ember.Application.extend({
    LOG_TRANSITIONS: config.LOG_TRANSITIONS,
    LOG_TRANSITIONS_INTERNAL: config.LOG_TRANSITIONS_INTERNAL,
    rootElement: '#modlr',
    modulePrefix: config.modulePrefix,
    podModulePrefix: config.podModulePrefix,
    Resolver
});

loadInitializers(App, config.modulePrefix);

export default App;
