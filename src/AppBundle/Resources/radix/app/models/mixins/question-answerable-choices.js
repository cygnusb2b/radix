import Ember       from 'ember';
import { hasMany } from 'ember-data/relationships';

const { Mixin } = Ember;

export default Mixin.create({
    value : hasMany('question-choice'),
});
