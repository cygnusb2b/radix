const { Schema } = require('mongoose');
const connection = require('../connections/mongoose/instance');
const { paginablePlugin, searchablePlugin } = require('../plugins');

const schema = new Schema({
  body: String,
  ipAddress: String,
  anonymize: Boolean,
  displayName: String,
  picture: String,
  banned: Boolean,
  approved: Boolean,
  _type: String,
  createdDate: Date,
  stream: {
    type: Schema.Types.ObjectId,
    ref: 'post-stream',
    validate: {
      async validator(id) {
        const doc = await connection.model('post-stream').findById(id, { _id: 1 });
        if (doc) return true;
        return false;
      },
      message: 'No post-stream record was found for ID {VALUE}',
    },
  },
  account: {
    type: Schema.Types.ObjectId,
    ref: 'identity',
    validate: {
      async validator(id) {
        const doc = await connection.model('identity').findById(id, { _id: 1 });
        if (doc) return true;
        return false;
      },
      message: 'No identity record was found for ID {VALUE}',
    },
  },

}, { timestamps: true, collection: 'post' });

schema.plugin(paginablePlugin);
schema.plugin(searchablePlugin, ['body', 'ipAddress', 'displayName']);

module.exports = schema;
