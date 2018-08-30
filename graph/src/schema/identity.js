const { Schema } = require('mongoose');
const { paginablePlugin, searchablePlugin } = require('../plugins');

const schema = new Schema({
  _type: String,

  displayName: String,
  familyName: String,
  givenName: String,
  picture: String,

  history: new Schema({
    lastLogin: Date,
    lastSeen: Date,
    logins: Number,
    remembers: Number,
  }),

  settings: new Schema({
    enabled: Boolean,
    locked: Boolean,
    shadowbanned: Boolean,
  }),

}, { timestamps: true, collection: 'identity' });

schema.plugin(paginablePlugin);
schema.plugin(searchablePlugin);

module.exports = schema;
