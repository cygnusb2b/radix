const { Schema } = require('mongoose');
const { paginablePlugin, searchablePlugin } = require('../plugins');

const schema = new Schema({
  application: Schema.Types.ObjectId,
  user: Schema.Types.ObjectId,
  roles: [{
    type: String,
    enum: ['ROLE_USER', 'ROLE_ADMIN'],
    default: 'ROLE_USER',
  }],
}, { collection: 'core-application-user' });


schema.plugin(paginablePlugin);
schema.plugin(searchablePlugin, { fieldNames: ['user.email', 'user.givenName', 'user.displayName'] });

module.exports = schema;
