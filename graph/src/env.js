const { isURL } = require('validator');

const {
  cleanEnv,
  makeValidator,
  port,
  bool,
  url,
} = require('envalid');

const mongodsn = makeValidator((v) => {
  const opts = { protocols: ['mongodb'], require_tld: false, require_protocol: true };
  if (isURL(v, opts)) return v;
  throw new Error('Expected a Mongo DSN string with mongodb://');
});

const nonemptystr = makeValidator((v) => {
  const err = new Error('Expected a non-empty string');
  if (v === undefined || v === null || v === '') {
    throw err;
  }
  const trimmed = String(v).trim();
  if (!trimmed) throw err;
  return trimmed;
});

module.exports = cleanEnv(process.env, {
  ACCOUNT_KEY: nonemptystr({ desc: 'The account:group app key', default: 'cygnus:ofcr' }),
  APP_HOST: nonemptystr({ desc: 'The hostname where the server instance is running.' }),
  ELASTIC_HOST: url({ desc: 'The Elasticsearch DSN to connect to.' }),
  ELASTIC_INDEX_RECREATE: bool({ desc: 'Whether to automatically create elasticsearch indices.', default: true }),
  MONGOOSE_DEBUG: bool({ desc: 'Whether to enable Mongoose debugging.', default: false }),
  MONGO_DSN: mongodsn({ desc: 'The MongoDB DSN to connect to.' }),
  PORT: port({ desc: 'The port that express will run on.', default: 8100 }),
});

