const mongoose = require('../connections/mongoose/core');
const schema = require('../schema/core-account');

module.exports = mongoose.model('core-account', schema);
