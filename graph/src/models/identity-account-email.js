const mongoose = require('../connections/mongoose/instance');
const schema = require('../schema/identity-account-email');

module.exports = mongoose.model('identity-account-email', schema);
