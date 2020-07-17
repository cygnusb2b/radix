const express = require('express');

const cors = require('cors');
const loadRouters = require('./routers');

const app = express();
const CORS = cors();

app.set('trust proxy', 'loopback, linklocal, uniquelocal');
app.disable('x-powered-by');

app.use(CORS);
app.options('*', CORS);

loadRouters(app);

module.exports = app;
