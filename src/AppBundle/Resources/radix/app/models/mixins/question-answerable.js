import Ember         from 'ember';
import { belongsTo } from 'ember-data/relationships';

const { Mixin } = Ember;

export default Mixin.create({
    question : belongsTo('question'),
});
