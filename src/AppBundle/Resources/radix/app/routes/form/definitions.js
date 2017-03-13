import Ember from 'ember';

const { inject: { service }, Route } = Ember;

export default Route.extend({

  query: service('model-query'),

  model() {
    return this.get('query').execute('form-definition');
  },

  actions: {
    loadTabs() {
      return [
        { key : 'general',      text : 'General',      icon : 'ion-document',            template : 'form/definitions/-general', active : true },
        // { key : 'answers',      text : 'Choices',      icon : 'ion-android-list',        template : 'demographic/questions/-answers'      },
        // { key : 'integrations', text : 'Integrations', icon : 'ion-wrench',              template : 'demographic/questions/-integrations' },
        { key : 'info',         text : 'Info',         icon : 'ion-information-circled', template : 'form/definitions/-info'         },
      ];
    },
  },
});
