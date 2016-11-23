import DS from 'ember-data';
import Ember from 'ember';

const { Mixin } = Ember;
const { hasMany } = DS;

export default Mixin.create({
    value : hasMany('question-choice'),
});
