import Object from '@ember/object';

export default Object.extend({
  all: null,

  unknownProperty() {
    const all = this.get('all');
    return true === all;
  },
});
