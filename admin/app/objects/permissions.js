import EmberObject from '@ember/object';
import Rules from 'radix/objects/permission-rules';

const { typeOf } = Ember;

export default EmberObject.extend({

  allowAll: false,

  fullAccess(bit = true) {
    this['allowAll'] = Boolean(bit);
  },

  set(key, rules) {
    const toSet = (typeOf(rules) === 'boolean') ? { all: rules } : rules;
    return this._super(key, Rules.create(toSet));
  },

  unknownProperty() {
    const toSet = (true === this.get('allowAll')) ? { all: true } : { };
    return Rules.create(toSet);
  },
});