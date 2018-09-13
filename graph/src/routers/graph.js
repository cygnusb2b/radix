const { Router } = require('express');
const bodyParser = require('body-parser');
const helmet = require('helmet');
const { graphqlExpress } = require('apollo-server-express');
const schema = require('../graph/schema');
const { instance } = require('../connections/mongoose');
const { CoreAccount, CoreApplication } = require('../models');

const router = Router();

const authenticate = (req, res, next) => {
  req.auth = {
    check() {
      // @todo!
      return true;
    },
  };
  next();
};

const setInstanceDatabase = async (req, res, next) => {
  const error = 'No valid application public key was presented with this request.';
  const publicKey = req.get('x-radix-appid');
  if (!publicKey) return res.status(401).send({ error });

  const application = await CoreApplication.findOne({ publicKey });
  if (!application) return res.status(401).send({ error });
  const applicationKey = application.get('key');

  const account = await CoreAccount.findById(application.account);
  if (!account) return res.status(500).send({ error: 'No account found for specified application.' });
  const accountKey = account.get('key');

  const dbName = `radix-${accountKey}-${applicationKey}`;
  req.db = await instance.setDb(dbName);
  next();
  return req.db;
};

router.use(
  helmet(),
  authenticate,
  setInstanceDatabase,
  bodyParser.json(),
  graphqlExpress((req) => {
    const { auth, db } = req;
    const appId = req.get('x-radix-appid');
    const context = { auth, db, appId };
    return { schema, context };
  }),
);

module.exports = router;
