/*jshint node:true*/
/* global require, module */
var EmberApp = require('ember-cli/lib/broccoli/ember-app');

module.exports = function(defaults) {
  var app = new EmberApp(defaults, {
    // Add options here
    'ember-cli-selectize': {
        // valid values are `default`, `bootstrap2`, `bootstrap3` or false
        'theme': 'bootstrap3'
    },
    fingerprint : { enabled : true }
  });

  // Bootstrap JS and source maps.
  // app.import('node_modules/bootstrap/dist/js/bootstrap.bundle.min.js');
  // app.import('node_modules/bootstrap/dist/js/bootstrap.bundle.min.js.map', { destDir: 'assets' });

  return app.toTree();
};
