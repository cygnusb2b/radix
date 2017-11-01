import Model from 'radix/models/post';
import { belongsTo } from 'ember-data/relationships';

export default Model.extend({
  parent: belongsTo('post-comment')
});