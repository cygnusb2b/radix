import Model         from 'ember-data/model';
import Keyable       from 'radix/models/mixins/keyable';
import Timestampable from 'radix/models/mixins/timestampable';

export default Model.extend(Keyable, Timestampable);
