const { Schema } = require('mongoose');
const { paginablePlugin, searchablePlugin } = require('../plugins');

const schema = new Schema({
  _type: String,

  value: String,
  isPrimary: Boolean,

  account: {
    type: Schema.Types.ObjectId,
    ref: 'identity',
  },

}, { timestamps: true, collection: 'identity-account-email' });

schema.plugin(paginablePlugin);
schema.plugin(searchablePlugin);

module.exports = schema;
