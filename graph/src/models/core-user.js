const mongoose = require('../connections/mongoose/core');
const schema = require('../schema/core-user');

module.exports = mongoose.model('core-user', schema);
