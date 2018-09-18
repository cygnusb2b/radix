const { Schema } = require('mongoose');

module.exports = new Schema({
  title: String,
  url: String,
  identifier: String,
  // ...
}, { timestamps: true, collection: 'post-stream' });
