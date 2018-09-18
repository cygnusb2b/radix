const mongoose = require('../connections/mongoose/instance');
const schema = require('../schema/identity');

module.exports = mongoose.model('identity', schema);
