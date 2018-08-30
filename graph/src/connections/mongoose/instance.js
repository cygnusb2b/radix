const mongoose = require('mongoose');
const bluebird = require('bluebird');
const output = require('../../output');
const env = require('../../env');

const { MONGO_DSN, MONGOOSE_DEBUG, APP } = env;
mongoose.set('debug', Boolean(MONGOOSE_DEBUG));
mongoose.Promise = bluebird;

const suffix = APP.replace(':', '-');
const instanceDSN = MONGO_DSN.replace('/radix', `/radix-${suffix}`);

const connection = mongoose.createConnection(instanceDSN, {
  // autoIndex: env.NODE_ENV !== 'production',
  ignoreUndefined: true,
  promiseLibrary: bluebird,
});
connection.once('open', () => output.write(`🛢️ 🛢️ 🛢️ Successful INSTANCE MongoDB connection to '${instanceDSN}'`));
module.exports = connection;
