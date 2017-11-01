import Ember from 'ember';

export default Ember.Object.extend({

  all: null,

  unknownProperty() {
    const all = this.get('all');
    if (true === all) {
      return true;
    }
    return false;
  },
});