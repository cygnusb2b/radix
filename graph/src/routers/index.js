const proxy = require('express-http-proxy');
const graph = require('./graph');

const { ENV, APP_HOST } = require('../env');

module.exports = (app) => {
  app.get('/', (req, res, err, next) => {
    if (ENV === 'prod') {
      res.redirect(301, '/manage');
      next();
    } else {
      proxy(APP_HOST, {
        preserveHostHdr: true,
      });
    }
  });
  app.use('/graph', graph);
  app.all('/*', proxy(APP_HOST, {
    preserveHostHdr: true,
  }));
};
