const { Schema } = require('mongoose');

module.exports = new Schema({
  name: String,
  key: String,
  publicKey: String,
  account: Schema.Types.ObjectId,
}, { collection: 'core-application' });
