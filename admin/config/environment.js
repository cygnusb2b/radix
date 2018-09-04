'use strict';

module.exports = function(environment) {
  let ENV = {
    modulePrefix: 'radix',
    environment,
    baseURL: '/',
    rootURL: '/manage/',
    locationType: 'auto',
    EmberENV: {
      FEATURES: {
        // Here you can enable experimental features on an ember canary build
        // e.g. 'with-controller': true
      },
      EXTEND_PROTOTYPES: {
        // Prevent Ember Data from overriding Date.parse.
        Date: false
      }
    },

    APP: {
      // Here you can pass flags/options to your application instance
      // when it is created
      name: 'radix',
    },

    formAnswerTypes: [
      {"value":"text","label":"A short, open-ended text answer (single line)"},
      {"value":"textarea","label":"A long, open-ended text answer (multiple lines)"},
      {"value":"choice-single","label":"A list of choices with a single answer"},
      {"value":"choice-multiple","label":"A list of choices with multiple answers"},
      {"value":"related-choice-single","label":"A list of choices from other questions, with a single answer"},
      {"value":"datetime","label":"A date answer with time"},
      {"value":"email","label":"An email address answer"},
      {"value":"float","label":"A number answer with decimals (float)"},
      {"value":"integer","label":"A number answer without decimals (integer)"},
      {"value":"boolean","label":"A yes or no answer (boolean)"},
      {"value":"url","label":"A url\/website answer"}
    ],

    simpleScheduleTypes: [
      {"value":"hourly","label":"Hourly"},
      {"value":"daily","label":"Daily"},
      {"value":"weekly","label":"Weekly"},
      {"value":"monthly","label":"Monthly"}
    ],

    formKeys: [
      {"value":"","label":""},
      {"value":"Inquiry","label":"Inquiry"},
      {"value":"Register","label":"Register"},
      {"value":"Gated Download","label":"Gated Download"},
      {"value":"Email Subscriptions","label":"Email Subscriptions"}
    ],

    apollo: {
      apiURL: '/graph',
    },
  };

  ENV.APP.LOG_TRANSITIONS = environment === 'development';
  ENV.APP.LOG_TRANSITIONS_INTERNAL = environment === 'development';

  if (environment === 'development') {
  }

  if (environment === 'test') {
    // Testem prefers this...
    ENV.locationType = 'none';

    // keep test console output quieter
    ENV.APP.LOG_ACTIVE_GENERATION = false;
    ENV.APP.LOG_VIEW_LOOKUPS = false;

    ENV.APP.rootElement = '#ember-testing';
    ENV.APP.autoboot = false;
  }

  if (environment === 'production') {
    // here you can enable a production-specific feature
  }

  return ENV;
};
