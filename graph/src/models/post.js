const mongoose = require('../connections/mongoose/instance');
const schema = require('../schema/post');

module.exports = mongoose.model('post', schema);
