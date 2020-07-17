const mongoose = require('mongoose');
const bluebird = require('bluebird');
const env = require('../../env');
const output = require('../../output');

const {
  MONGO_DSN,
  MONGOOSE_DEBUG,
} = env;
mongoose.set('debug', Boolean(MONGOOSE_DEBUG));
mongoose.Promise = bluebird;

const connection = mongoose.createConnection(MONGO_DSN, {
  // autoIndex: env.NODE_ENV !== 'production',
  ignoreUndefined: true,
  promiseLibrary: bluebird,
});
connection.once('open', () => {
  output.write(`🛢️ 🛢️ 🛢️ Successful CORE MongoDB connection to '${MONGO_DSN}'`);
});
module.exports = connection;
