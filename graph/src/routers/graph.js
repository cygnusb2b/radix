const { Router } = require('express');
const bodyParser = require('body-parser');
const helmet = require('helmet');
const { graphqlExpress } = require('apollo-server-express');
const schema = require('../graph/schema');

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

router.use(
  helmet(),
  authenticate,
  bodyParser.json(),
  graphqlExpress((req) => {
    const { auth, db } = req;
    const context = { auth, db };
    return { schema, context };
  }),
);

module.exports = router;
