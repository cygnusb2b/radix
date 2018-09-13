const { Schema } = require('mongoose');

module.exports = new Schema({
  email: String,
  givenName: String,
  familyName: String,
  createdDate: Date,
  updatedDate: Date,
  lastSeen: Date,
}, { collection: 'core-user' });
