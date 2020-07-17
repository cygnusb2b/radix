const { Schema } = require('mongoose');

module.exports = new Schema({
  name: String,
  key: String,
}, { collection: 'core-account' });
