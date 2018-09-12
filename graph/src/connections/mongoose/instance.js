const mongoose = require('mongoose');
const bluebird = require('bluebird');
const output = require('../../output');
const env = require('../../env');

const { MONGO_DSN, MONGOOSE_DEBUG } = env;
mongoose.set('debug', Boolean(MONGOOSE_DEBUG));
mongoose.Promise = bluebird;

const connectOpts = {
  ignoreUndefined: true,
  promiseLibrary: bluebird,
};

const instanceDSN = MONGO_DSN.replace('/radix', '/radix-test-init');
const connection = mongoose.createConnection(instanceDSN, connectOpts);
connection.on('open', () => output.write(`🛢️ 🛢️ 🛢️ Successful INSTANCE MongoDB connection to '${instanceDSN}'`));

connection.setDb = async (dbName) => {
  const db = await connection.useDb(dbName);
  const names = connection.modelNames();
  console.warn(names);
  await names.forEach(async n => db.model(n, require(`../../schema/${n}`))); // eslint-disable-line
  return db;
};

module.exports = connection;
