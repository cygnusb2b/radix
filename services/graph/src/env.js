const { isURL } = require('validator');

const {
  cleanEnv,
  makeValidator,
  port,
  bool,
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
  APP_HOST: nonemptystr({ desc: 'The hostname where the server instance is running.' }),
  MONGOOSE_DEBUG: bool({ desc: 'Whether to enable Mongoose debugging.', default: false }),
  MONGO_DSN: mongodsn({ desc: 'The MongoDB DSN to connect to.' }),
  PORT: port({ desc: 'The port that express will run on.', default: 8100 }),
  SENDGRID_API_KEY: nonemptystr({ desc: 'The Sendgrid email API key' }),
  SENDGRID_FROM: nonemptystr({ desc: 'The From: address for Sendgrid emails' }),
  JWT_SECRET: nonemptystr({ desc: 'The secret key used to encode Json Web Tokens' }),
});

