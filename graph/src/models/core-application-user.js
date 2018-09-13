const mongoose = require('../connections/mongoose/core');
const schema = require('../schema/core-application-user');

module.exports = mongoose.model('core-application-user', schema);
