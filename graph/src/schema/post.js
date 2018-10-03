const { Schema } = require('mongoose');
const { paginablePlugin, searchablePlugin } = require('../plugins');

const schema = new Schema({
  body: String,
  ipAddress: String,
  anonymize: Boolean,
  displayName: String,
  picture: String,
  title: String,
  rating: Number,
  banned: Boolean,
  approved: Boolean,
  deleted: Boolean,
  flagged: Boolean,
  _type: String,
  createdDate: Date,
  stream: {
    type: Schema.Types.ObjectId,
    ref: 'post-stream',
  },
  account: {
    type: Schema.Types.ObjectId,
    ref: 'identity',
  },

}, { timestamps: true, collection: 'post' });

schema.plugin(paginablePlugin);
schema.plugin(searchablePlugin, { fieldNames: ['body', 'ipAddress', 'displayName'] });

module.exports = schema;
