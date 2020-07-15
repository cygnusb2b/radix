import EmberObject from '@ember/object';
import Rules from 'radix/objects/permission-rules';
import { typeOf } from '@ember/utils';
import { defineProperty } from '@ember/object';

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

  setUnknownProperty(key, value) {
    const toSet = (true === this.get('allowAll')) ? { all: true } : value;
    const rule = Rules.create(toSet);
    defineProperty(this, key, null, rule);
    return rule;
  }

});
