const mongoose = require('../connections/mongoose/instance');
const schema = require('../schema/post-stream');

module.exports = mongoose.model('post-stream', schema);
