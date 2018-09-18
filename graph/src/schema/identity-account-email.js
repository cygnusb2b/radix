const { Schema } = require('mongoose');
const connection = require('../connections/mongoose/instance');
const { paginablePlugin, searchablePlugin } = require('../plugins');

const schema = new Schema({
  _type: String,

  value: String,
  isPrimary: Boolean,

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

}, { timestamps: true, collection: 'identity-account-email' });

schema.plugin(paginablePlugin);
schema.plugin(searchablePlugin);

module.exports = schema;
