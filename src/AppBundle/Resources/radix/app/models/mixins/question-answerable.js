import DS from 'ember-data';
import Ember from 'ember';

const { Mixin } = Ember;
const { belongsTo } = DS;

export default Mixin.create({
    question : belongsTo('question'),
});
