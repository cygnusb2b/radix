const { Router } = require('express');
const bodyParser = require('body-parser');
const helmet = require('helmet');
const { graphqlExpress } = require('apollo-server-express');
const schema = require('../graph/schema');

const router = Router();

router.use(
  helmet(),
  // authenticate,
  bodyParser.json(),
  graphqlExpress(() => {
    const context = { };
    return { schema, context };
  }),
);

module.exports = router;
