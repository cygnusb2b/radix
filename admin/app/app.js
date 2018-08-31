import Application from '@ember/application';
import LinkComponent from '@ember/routing/link-component'
import Resolver from './resolver';
import loadInitializers from 'ember-load-initializers';
import config from './config/environment';

const App = Application.extend({
  modulePrefix: config.modulePrefix,
  podModulePrefix: config.podModulePrefix,
  Resolver
});

loadInitializers(App, config.modulePrefix);

LinkComponent.reopen({
  attributeBindings: ['data-toggle']
});

export default App;
